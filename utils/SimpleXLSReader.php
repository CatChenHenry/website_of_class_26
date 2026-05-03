<?php
/**
 * 轻量级 XLS 读取器
 * 纯 PHP 实现，不依赖任何扩展
 * 支持 Excel 97-2003 (.xls) BIFF5/BIFF8 格式
 */
class SimpleXLSReader
{
    private $data = '';
    private $length = 0;

    /**
     * 解析 xls 文件
     * @param string $filePath 文件路径
     * @return array|false 二维数组或 false
     */
    public static function parse($filePath)
    {
        $reader = new self();
        return $reader->read($filePath);
    }

    private function read($filePath)
    {
        if (!is_readable($filePath)) {
            return false;
        }

        $this->data = file_get_contents($filePath);
        if ($this->data === false || strlen($this->data) < 512) {
            return false;
        }
        $this->length = strlen($this->data);

        // 检查 OLE2 签名
        $sig = unpack('v', substr($this->data, 0, 2))[1];
        if ($sig !== 0xE11A) {
            // 再检查一下完整签名
            $magic = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
            if (substr($this->data, 0, 8) !== $magic) {
                return false;
            }
        }

        // 提取 Workbook 流
        $workbook = $this->extractWorkbookStream();
        if ($workbook === false) {
            return false;
        }

        return $this->parseBIFF($workbook);
    }

    /**
     * 从 OLE2 Compound Binary File 中提取 Workbook 流
     */
    private function extractWorkbookStream()
    {
        // 解析 Header
        $minorVersion = unpack('v', substr($this->data, 24, 2))[1];
        $majorVersion = unpack('v', substr($this->data, 26, 2))[1];
        $byteOrder = unpack('v', substr($this->data, 28, 2))[1];
        $sectorSizePow = unpack('v', substr($this->data, 30, 2))[1];
        $sectorSize = 1 << $sectorSizePow;

        $totalFATSectors = unpack('V', substr($this->data, 44, 4))[1];
        $firstDirSectorSID = $this->readSIGNEDorUNSIGNED(substr($this->data, 48, 4), $majorVersion);
        $firstMiniFATSectorSID = $this->readSIGNEDorUNSIGNED(substr($this->data, 60, 4), $majorVersion);
        $miniStreamCutoff = unpack('V', substr($this->data, 64, 4))[1];
        $firstMiniFATSector = unpack('V', substr($this->data, 60, 4))[1];

        // 读取 DIFAT (从 header)
        $difat = [];
        for ($i = 0; $i < 109; $i++) {
            $sid = unpack('V', substr($this->data, 76 + $i * 4, 4))[1];
            if ($sid != 0xFFFFFFFE && $sid != 0xFFFFFFFF) {
                $difat[] = $sid;
            }
        }

        // 读取额外的 DIFAT 链
        $nextDIFAT = $this->readSIGNEDorUNSIGNED(substr($this->data, 68, 4), $majorVersion);
        while ($nextDIFAT != 0xFFFFFFFE && $nextDIFAT != 0xFFFFFFFF && $nextDIFAT >= 0 && count($difat) < $totalFATSectors) {
            $sectorOffset = 512 + $nextDIFAT * $sectorSize; // DIFAT sectors start after header
            if ($sectorOffset + $sectorSize > $this->length) break;

            $entriesPerSector = floor($sectorSize / 4) - 1;
            for ($i = 0; $i < $entriesPerSector; $i++) {
                $sid = unpack('V', substr($this->data, $sectorOffset + $i * 4, 4))[1];
                if ($sid != 0xFFFFFFFE && $sid != 0xFFFFFFFF) {
                    $difat[] = $sid;
                }
            }
            $nextDIFAT = unpack('V', substr($this->data, $sectorOffset + $entriesPerSector * 4, 4))[1];
            if ($nextDIFAT == 0xFFFFFFFE) $nextDIFAT = -2;
        }

        // 构建 FAT
        $fat = [];
        foreach ($difat as $fatSectorSID) {
            if ($fatSectorSID < 0) continue;
            $offset = 512 + $fatSectorSID * $sectorSize;
            if ($offset + $sectorSize > $this->length) continue;
            $entries = $sectorSize / 4;
            for ($i = 0; $i < $entries; $i++) {
                $fat[] = unpack('V', substr($this->data, $offset + $i * 4, 4))[1];
            }
        }

        // 读取 Directory
        $dirEntries = $this->readDirEntries($firstDirSectorSID, $fat, $sectorSize);

        // 查找 Workbook 或 Book 入口
        $workbookEntry = null;
        $rootEntry = null;
        foreach ($dirEntries as $entry) {
            if ($entry['name'] === 'Workbook' || $entry['name'] === 'Book') {
                $workbookEntry = $entry;
            }
            if ($entry['name'] === 'Root Entry') {
                $rootEntry = $entry;
            }
        }

        if ($workbookEntry === null) {
            return false;
        }

        // 提取流数据
        if ($workbookEntry['startSID'] == 0xFFFFFFFE || $workbookEntry['startSID'] == 0xFFFFFFFF) {
            return '';
        }

        // 如果是 Mini Stream
        if ($workbookEntry['size'] > 0 && $workbookEntry['size'] < $miniStreamCutoff && $rootEntry !== null) {
            // 先构建 Mini FAT
            $miniFAT = $this->buildMiniFAT($firstMiniFATSectorSID, $fat, $sectorSize);
            if (!empty($miniFAT)) {
                $miniStreamData = $this->readStream($rootEntry['startSID'], $rootEntry['size'], $fat, $sectorSize);
                if ($miniStreamData !== false) {
                    return $this->readMiniStream($workbookEntry['startSID'], $workbookEntry['size'], $miniFAT, $miniStreamData, 64);
                }
            }
        }

        return $this->readStream($workbookEntry['startSID'], $workbookEntry['size'], $fat, $sectorSize);
    }

    private function readSIGNEDorUNSIGNED($bytes, $majorVersion)
    {
        $val = unpack('V', $bytes)[1];
        if ($majorVersion == 4) {
            // v4 uses signed 32-bit
            if ($val >= 0x80000000) {
                $val = $val - 0x100000000;
            }
        }
        return $val;
    }

    private function readDirEntries($firstDirSectorSID, &$fat, $sectorSize)
    {
        $entries = [];
        $sectorData = $this->readStream($firstDirSectorSID, 0, $fat, $sectorSize);
        if ($sectorData === false) return $entries;

        $entrySize = 128;
        $numEntries = floor(strlen($sectorData) / $entrySize);

        for ($i = 0; $i < $numEntries; $i++) {
            $offset = $i * $entrySize;
            $entryData = substr($sectorData, $offset, $entrySize);

            $nameLen = unpack('v', substr($entryData, 64, 2))[1];
            if ($nameLen == 0) continue;

            $nameBytes = substr($entryData, 0, $nameLen);
            // UTF-16LE to UTF-8
            $name = mb_convert_encoding($nameBytes, 'UTF-8', 'UTF-16LE');
            $name = rtrim($name, "\x00");

            $objectType = ord($entryData[66]);
            $startSID = unpack('V', substr($entryData, 116, 4))[1];
            $size = unpack('V', substr($entryData, 120, 4))[1];

            if ($objectType == 0) continue; // empty

            $entries[] = [
                'name' => $name,
                'type' => $objectType,
                'startSID' => $startSID,
                'size' => $size
            ];
        }

        return $entries;
    }

    private function buildMiniFAT($firstMiniFATSector, &$fat, $sectorSize)
    {
        $miniFAT = [];
        if ($firstMiniFATSector == 0xFFFFFFFE || $firstMiniFATSector == 0xFFFFFFFF) {
            return $miniFAT;
        }

        $sid = $firstMiniFATSector;
        $visited = [];
        while ($sid != 0xFFFFFFFE && $sid != 0xFFFFFFFF && $sid >= 0 && !isset($visited[$sid])) {
            $visited[$sid] = true;
            $offset = 512 + $sid * $sectorSize;
            if ($offset + $sectorSize > $this->length) break;

            $entries = $sectorSize / 4;
            for ($i = 0; $i < $entries; $i++) {
                $miniFAT[] = unpack('V', substr($this->data, $offset + $i * 4, 4))[1];
            }

            $sid = isset($fat[$sid]) ? $fat[$sid] : 0xFFFFFFFE;
        }

        return $miniFAT;
    }

    private function readStream($startSID, $size, &$fat, $sectorSize)
    {
        $result = '';
        $sid = $startSID;
        $visited = [];

        while ($sid != 0xFFFFFFFE && $sid != 0xFFFFFFFF && $sid >= 0 && !isset($visited[$sid])) {
            $visited[$sid] = true;
            $offset = 512 + $sid * $sectorSize;
            if ($offset + $sectorSize > $this->length) break;

            $result .= substr($this->data, $offset, $sectorSize);

            if (!isset($fat[$sid])) break;
            $sid = $fat[$sid];
        }

        if ($size > 0 && strlen($result) > $size) {
            $result = substr($result, 0, $size);
        }

        return $result;
    }

    private function readMiniStream($startSID, $size, &$miniFAT, &$miniStreamData, $miniSectorSize)
    {
        $result = '';
        $sid = $startSID;
        $visited = [];

        while ($sid != 0xFFFFFFFE && $sid != 0xFFFFFFFF && $sid >= 0 && !isset($visited[$sid])) {
            $visited[$sid] = true;
            $offset = $sid * $miniSectorSize;
            if ($offset + $miniSectorSize > strlen($miniStreamData)) break;

            $result .= substr($miniStreamData, $offset, $miniSectorSize);

            if (!isset($miniFAT[$sid])) break;
            $sid = $miniFAT[$sid];
        }

        if ($size > 0 && strlen($result) > $size) {
            $result = substr($result, 0, $size);
        }

        return $result;
    }

    /**
     * 解析 BIFF 数据
     */
    private function parseBIFF(&$data)
    {
        $pos = 0;
        $dataLen = strlen($data);

        // 检测 BIFF 版本
        $biffVersion = 0;
        $isBIFF8 = false;

        // SST (Shared String Table) for BIFF8
        $sst = [];
        $codepage = 'UTF-8';

        // 当前解析的行
        $rows = [];
        $currentRow = -1;

        // 记录公式字符串缓存 (FORMULA record with string result)
        $formulaStringResults = [];

        while ($pos + 4 <= $dataLen) {
            $recordType = unpack('v', substr($data, $pos, 2))[1];
            $recordSize = unpack('v', substr($data, $pos + 2, 2))[1];
            $recordData = substr($data, $pos + 4, $recordSize);

            if ($pos + 4 + $recordSize > $dataLen) break;

            switch ($recordType) {
                case 0x0809: // BOF
                    if ($recordSize >= 2) {
                        $biffVersion = unpack('v', substr($recordData, 0, 2))[1];
                        $isBIFF8 = ($biffVersion == 0x0600);
                    }
                    break;

                case 0x0042: // CODEPAGE
                    if ($recordSize >= 2) {
                        $cp = unpack('v', substr($recordData, 0, 2))[1];
                        $codepage = $this->codepageToEncoding($cp);
                    }
                    break;

                case 0x00FC: // SST (Shared String Table) - BIFF8
                    if ($isBIFF8 && $recordSize > 0) {
                        $sst = $this->parseSST($recordData, $recordSize);
                    }
                    break;

                case 0x00FD: // CONTINUE
                    // Handled within SST parsing already; skip standalone
                    break;

                case 0x0203: // NUMBER - BIFF2/3/4/5/8
                    if ($recordSize >= 14) {
                        $row = unpack('v', substr($recordData, 0, 2))[1];
                        $col = unpack('v', substr($recordData, 2, 2))[1];

                        // 8-byte IEEE 754 double
                        $raw = substr($recordData, 6, 8);
                        $value = $this->readDouble($raw);

                        if (!isset($rows[$row])) $rows[$row] = [];
                        $rows[$row][$col] = $this->formatNumber($value);
                    }
                    break;

                case 0x027E: // RK - BIFF2/3/4/5/8
                    if ($recordSize >= 10) {
                        $row = unpack('v', substr($recordData, 0, 2))[1];
                        $col = unpack('v', substr($recordData, 2, 2))[1];
                        $rk = $this->readRK(substr($recordData, 6, 4));

                        if (!isset($rows[$row])) $rows[$row] = [];
                        $rows[$row][$col] = $this->formatNumber($rk);
                    }
                    break;

                case 0x00BD: // MULRK - multiple RK values
                    if ($recordSize >= 6) {
                        $row = unpack('v', substr($recordData, 0, 2))[1];
                        $firstCol = unpack('v', substr($recordData, 2, 2))[1];
                        $lastCol = unpack('v', substr($recordData, $recordSize - 2, 2))[1];

                        for ($c = $firstCol; $c <= $lastCol; $c++) {
                            $offset = 4 + ($c - $firstCol) * 6;
                            if ($offset + 6 > $recordSize) break;
                            $rk = $this->readRK(substr($recordData, $offset + 2, 4));

                            if (!isset($rows[$row])) $rows[$row] = [];
                            $rows[$row][$c] = $this->formatNumber($rk);
                        }
                    }
                    break;

                case 0x0204: // LABEL (BIFF2/3/4/5) - short string
                    if ($recordSize >= 8) {
                        $row = unpack('v', substr($recordData, 0, 2))[1];
                        $col = unpack('v', substr($recordData, 2, 2))[1];
                        $strLen = unpack('v', substr($recordData, 6, 2))[1];
                        $strData = substr($recordData, 8, $strLen);

                        if (!isset($rows[$row])) $rows[$row] = [];
                        $rows[$row][$col] = $this->decodeString($strData, $codepage, false);
                    }
                    break;

                case 0x00FD: // LABELSST - BIFF8 string via SST index
                    if ($isBIFF8 && $recordSize >= 6) {
                        $row = unpack('v', substr($recordData, 0, 2))[1];
                        $col = unpack('v', substr($recordData, 2, 2))[1];
                        $sstIndex = unpack('V', substr($recordData, 6, 4))[1];

                        $value = isset($sst[$sstIndex]) ? $sst[$sstIndex] : '';

                        if (!isset($rows[$row])) $rows[$row] = [];
                        $rows[$row][$col] = $value;
                    }
                    break;

                case 0x0006: // FORMULA
                    if ($recordSize >= 14) {
                        $row = unpack('v', substr($recordData, 0, 2))[1];
                        $col = unpack('v', substr($recordData, 2, 2))[1];

                        // Check if result is a string (magic identifier at offset 6)
                        $resultFlag = ord($recordData[6]);
                        if ($resultFlag == 0xFF) {
                            // String result - will be in a STRING record following
                            // For now, mark as empty; the STRING record follows
                            // We'll handle the STRING record
                            if (!isset($rows[$row])) $rows[$row] = [];
                            $rows[$row][$col] = '';
                            $formulaStringResults[$row . '_' . $col] = true;
                        } else {
                            // Numeric result
                            $raw = substr($recordData, 6, 8);
                            $value = $this->readDouble($raw);

                            if (!isset($rows[$row])) $rows[$row] = [];
                            $rows[$row][$col] = $this->formatNumber($value);
                        }
                    }
                    break;

                case 0x0207: // STRING - formula string result
                    // This follows a FORMULA record with string result
                    // Find the last formula cell that needs a string
                    if ($recordSize >= 3 && !empty($formulaStringResults)) {
                        $str = $this->parseBIFF8String($recordData, 0, $recordSize);
                        // Apply to the last pending formula cell
                        $lastKey = array_key_last($formulaStringResults);
                        if ($lastKey !== null) {
                            list($r, $c) = explode('_', $lastKey);
                            $r = (int)$r;
                            $c = (int)$c;
                            if (isset($rows[$r])) {
                                $rows[$r][$c] = $str;
                            }
                            unset($formulaStringResults[$lastKey]);
                        }
                    }
                    break;

                case 0x000A: // EOF
                    break;
            }

            $pos += 4 + $recordSize;
        }

        // 转换为连续二维数组
        return $this->rowsToArray($rows);
    }

    /**
     * 解析 SST (Shared String Table)
     */
    private function parseSST(&$data, $dataSize)
    {
        $strings = [];
        if ($dataSize < 8) return $strings;

        $totalStrings = unpack('V', substr($data, 0, 4))[1];
        $uniqueStrings = unpack('V', substr($data, 4, 4))[1];

        $pos = 8;
        $count = 0;

        while ($pos < $dataSize && $count < $uniqueStrings) {
            $result = $this->parseBIFF8StringWithLength($data, $pos, $dataSize);
            if ($result === false) break;
            $strings[] = $result['value'];
            $pos = $result['nextPos'];
            $count++;
        }

        return $strings;
    }

    /**
     * 解析 BIFF8 字符串（带长度前缀）
     */
    private function parseBIFF8StringWithLength(&$data, $pos, $dataSize)
    {
        if ($pos + 3 > $dataSize) return false;

        $strLen = unpack('v', substr($data, $pos, 2))[1];
        $flags = ord($data[$pos + 2]);

        $isUnicode = ($flags & 0x01) != 0;
        $hasRichText = ($flags & 0x08) != 0;
        $hasExtString = ($flags & 0x04) != 0;

        $pos += 3;

        if ($hasRichText && $pos + 2 <= $dataSize) {
            $pos += 2; // skip rich text run count
        }
        if ($hasExtString && $pos + 4 <= $dataSize) {
            $pos += 4; // skip ext string size
        }

        $byteLen = $isUnicode ? $strLen * 2 : $strLen;

        if ($pos + $byteLen > $dataSize) {
            // Try to get as much as we can
            $byteLen = $dataSize - $pos;
            if ($isUnicode) $byteLen = floor($byteLen / 2) * 2;
        }

        $rawStr = substr($data, $pos, $byteLen);

        if ($isUnicode) {
            $value = mb_convert_encoding($rawStr, 'UTF-8', 'UTF-16LE');
        } else {
            $value = mb_convert_encoding($rawStr, 'UTF-8', 'CP1252');
        }

        $pos += $byteLen;

        if ($hasRichText) {
            // Skip rich text data
            // We'd need the run count, but we already consumed it
        }

        return ['value' => $value, 'nextPos' => $pos];
    }

    /**
     * 解析 BIFF8 字符串（无长度前缀，用于 STRING record）
     */
    private function parseBIFF8String(&$data, $offset, $size)
    {
        if ($offset + 3 > $size) return '';

        $strLen = unpack('v', substr($data, $offset, 2))[1];
        $flags = ord($data[$offset + 2]);

        $isUnicode = ($flags & 0x01) != 0;
        $hasRichText = ($flags & 0x08) != 0;
        $hasExtString = ($flags & 0x04) != 0;

        $pos = $offset + 3;

        $richTextRuns = 0;
        $extSize = 0;

        if ($hasRichText && $pos + 2 <= $size) {
            $richTextRuns = unpack('v', substr($data, $pos, 2))[1];
            $pos += 2;
        }
        if ($hasExtString && $pos + 4 <= $size) {
            $extSize = unpack('V', substr($data, $pos, 4))[1];
            $pos += 4;
        }

        $byteLen = $isUnicode ? $strLen * 2 : $strLen;
        if ($pos + $byteLen > $size) {
            $byteLen = max(0, $size - $pos);
            if ($isUnicode) $byteLen = floor($byteLen / 2) * 2;
        }

        $rawStr = substr($data, $pos, $byteLen);
        if ($isUnicode) {
            return mb_convert_encoding($rawStr, 'UTF-8', 'UTF-16LE');
        } else {
            return mb_convert_encoding($rawStr, 'UTF-8', 'CP1252');
        }
    }

    /**
     * 读取 RK 编码值
     */
    private function readRK($bytes)
    {
        $rk = unpack('V', $bytes)[1];
        $isInt = ($rk & 0x02) != 0;
        $isDiv100 = ($rk & 0x01) != 0;

        if ($isInt) {
            $value = ($rk >> 2);
            // Sign extend 30-bit integer
            if ($value & 0x20000000) {
                $value -= 0x40000000;
            }
        } else {
            // 10-byte IEEE float with modified high dword
            $low = $rk & 0xFFFFFFFC;
            // Reconstruct 8-byte double: high 4 bytes = low (with bits 0,1 cleared), low 4 bytes = 0
            $packed = pack('VV', 0, $low);
            $value = $this->readDouble($packed);
        }

        if ($isDiv100) {
            $value /= 100.0;
        }

        return $value;
    }

    /**
     * 读取 8 字节 IEEE 754 双精度浮点数
     */
    private function readDouble($bytes)
    {
        if (strlen($bytes) < 8) return 0.0;

        // Use unpack with 'd' for little-endian double
        $result = unpack('d', $bytes);
        return $result[1];
    }

    /**
     * 格式化数字（去掉不必要的小数位）
     */
    private function formatNumber($value)
    {
        if ($value == (int)$value) {
            return (string)(int)$value;
        }
        // 最多保留6位小数，去掉尾部的0
        return rtrim(rtrim(sprintf('%.6f', $value), '0'), '.');
    }

    /**
     * 解码字符串
     */
    private function decodeString($bytes, $codepage, $isUnicode)
    {
        if ($isUnicode) {
            return mb_convert_encoding($bytes, 'UTF-8', 'UTF-16LE');
        }
        if ($codepage !== 'UTF-8') {
            $converted = @mb_convert_encoding($bytes, 'UTF-8', $codepage);
            if ($converted !== false) return $converted;
        }
        return $bytes;
    }

    /**
     * 代码页转编码名称
     */
    private function codepageToEncoding($codepage)
    {
        $map = [
            0x04B0 => 'UTF-16LE',
            0x04E4 => 'CP1252',
            0x04E3 => 'CP1251',
            0x04E2 => 'CP1250',
            0x03A8 => 'CP936',  // GBK
            0x03A4 => 'CP950',  // Big5
            0x03B5 => 'CP932',  // Shift_JIS
            0x03B6 => 'CP949',  // Korean
        ];
        return isset($map[$codepage]) ? $map[$codepage] : 'CP1252';
    }

    /**
     * 将稀疏行数据转为连续二维数组
     */
    private function rowsToArray(&$rows)
    {
        if (empty($rows)) return [];

        ksort($rows); // 按行号排序

        $maxCol = 0;
        foreach ($rows as $cols) {
            if (!empty($cols)) {
                $maxCol = max($maxCol, max(array_keys($cols)));
            }
        }

        $result = [];
        foreach ($rows as $rowIdx => $cols) {
            $row = [];
            for ($c = 0; $c <= $maxCol; $c++) {
                $row[] = isset($cols[$c]) ? $cols[$c] : '';
            }

            // 跳过完全为空的行
            $isEmpty = true;
            foreach ($row as $cell) {
                if ($cell !== '') {
                    $isEmpty = false;
                    break;
                }
            }
            if (!$isEmpty) {
                $result[] = $row;
            }
        }

        return $result;
    }
}
