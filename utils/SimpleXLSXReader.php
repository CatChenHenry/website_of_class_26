<?php
/**
 * 轻量级 XLSX 读取器
 * 不依赖 ZipArchive 等扩展，纯 PHP 实现
 * 仅支持读取第一个工作表的纯文本数据
 */
class SimpleXLSXReader
{
    private $rows = [];

    /**
     * 解析 xlsx 文件
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

        $content = file_get_contents($filePath);
        if ($content === false || strlen($content) < 4) {
            return false;
        }

        // 检查 ZIP 签名
        $sig = substr($content, 0, 4);
        if ($sig !== "PK\x03\x04") {
            return false;
        }

        // 从 ZIP 中提取 sharedStrings.xml 和 sheet1.xml
        $sharedStrings = $this->extractZipEntry($content, 'xl/sharedStrings.xml');
        $sheet1 = $this->extractZipEntry($content, 'xl/worksheets/sheet1.xml');

        // 如果 sheet1 不在标准路径，尝试搜索
        if ($sheet1 === false) {
            $sheet1 = $this->extractZipEntryByPattern($content, 'worksheets/sheet1.xml');
        }
        if ($sheet1 === false) {
            $sheet1 = $this->extractZipEntryByPattern($content, 'sheet1.xml');
        }

        if ($sheet1 === false) {
            return false;
        }

        // 解析共享字符串表
        $strings = [];
        if ($sharedStrings !== false) {
            $strings = $this->parseSharedStrings($sharedStrings);
        }

        // 解析工作表
        return $this->parseSheet($sheet1, $strings);
    }

    /**
     * 从 ZIP 内容中提取指定文件
     */
    private function extractZipEntry($zipContent, $entryName)
    {
        // 查找 Local File Header
        $offset = 0;
        $len = strlen($zipContent);

        while ($offset < $len - 30) {
            // 检查 Local File Header 签名
            $sig = substr($zipContent, $offset, 4);
            if ($sig !== "PK\x03\x04") {
                break;
            }

            // 解析 Local File Header
            $compMethod = $this->readU16($zipContent, $offset + 8);
            $compSize = $this->readU32($zipContent, $offset + 18);
            $uncompSize = $this->readU32($zipContent, $offset + 22);
            $nameLen = $this->readU16($zipContent, $offset + 26);
            $extraLen = $this->readU16($zipContent, $offset + 28);

            $fileName = substr($zipContent, $offset + 30, $nameLen);
            $dataOffset = $offset + 30 + $nameLen + $extraLen;

            if ($fileName === $entryName) {
                $compressedData = substr($zipContent, $dataOffset, $compSize);

                if ($compMethod === 0) {
                    // STORE - 无压缩
                    return $compressedData;
                } elseif ($compMethod === 8) {
                    // DEFLATE
                    $decompressed = @gzinflate($compressedData);
                    if ($decompressed !== false) {
                        return $decompressed;
                    }
                    // 尝试带 raw 格式
                    $decompressed = @gzinflate(substr($compressedData, 2, -4));
                    if ($decompressed !== false) {
                        return $decompressed;
                    }
                    return false;
                }
                return false;
            }

            $offset = $dataOffset + $compSize;
        }

        return false;
    }

    /**
     * 按模式搜索并提取 ZIP 条目
     */
    private function extractZipEntryByPattern($zipContent, $pattern)
    {
        $offset = 0;
        $len = strlen($zipContent);
        $candidates = [];

        while ($offset < $len - 30) {
            $sig = substr($zipContent, $offset, 4);
            if ($sig !== "PK\x03\x04") {
                break;
            }

            $compMethod = $this->readU16($zipContent, $offset + 8);
            $compSize = $this->readU32($zipContent, $offset + 18);
            $nameLen = $this->readU16($zipContent, $offset + 26);
            $extraLen = $this->readU16($zipContent, $offset + 28);

            $fileName = substr($zipContent, $offset + 30, $nameLen);
            $dataOffset = $offset + 30 + $nameLen + $extraLen;

            if (stripos($fileName, $pattern) !== false) {
                $compressedData = substr($zipContent, $dataOffset, $compSize);

                $data = false;
                if ($compMethod === 0) {
                    $data = $compressedData;
                } elseif ($compMethod === 8) {
                    $data = @gzinflate($compressedData);
                    if ($data === false) {
                        $data = @gzinflate(substr($compressedData, 2, -4));
                    }
                }

                if ($data !== false) {
                    $candidates[] = $data;
                }
            }

            $offset = $dataOffset + $compSize;
        }

        return !empty($candidates) ? $candidates[0] : false;
    }

    private function readU16($data, $offset)
    {
        return ord($data[$offset]) | (ord($data[$offset + 1]) << 8);
    }

    private function readU32($data, $offset)
    {
        return ord($data[$offset]) | (ord($data[$offset + 1]) << 8) | (ord($data[$offset + 2]) << 16) | (ord($data[$offset + 3]) << 24);
    }

    /**
     * 解析共享字符串表
     */
    private function parseSharedStrings($xml)
    {
        $strings = [];

        // 使用简单的正则提取 <si>...</si> 中的文本
        if (preg_match_all('/<si\b[^>]*>(.*?)<\/si>/s', $xml, $siMatches)) {
            foreach ($siMatches[1] as $siContent) {
                $text = '';
                // 提取 <t> 标签内容（可能有多个 <t> 用于富文本）
                if (preg_match_all('/<t\b[^>]*>(.*?)<\/t>/s', $siContent, $tMatches)) {
                    $text = implode('', $tMatches[1]);
                }
                // 处理 XML 实体
                $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $strings[] = $text;
            }
        }

        return $strings;
    }

    /**
     * 解析工作表
     */
    private function parseSheet($xml, $sharedStrings)
    {
        $rows = [];

        if (!preg_match_all('/<row\b[^>]*>(.*?)<\/row>/s', $xml, $rowMatches)) {
            return [];
        }

        foreach ($rowMatches[1] as $rowContent) {
            $cells = [];

            // 匹配带内容的单元格 <c ...>...</c> 和自关闭的空单元格 <c .../>
            if (preg_match_all('/<c\b([^>]*?)(\/>|>(.*?)<\/c>)/s', $rowContent, $cellMatches, PREG_SET_ORDER)) {
                foreach ($cellMatches as $cellMatch) {
                    $attrs = $cellMatch[1];
                    $isSelfClosing = substr($cellMatch[2], 0, 1) === '/';
                    $cellContent = $isSelfClosing ? '' : (isset($cellMatch[3]) ? $cellMatch[3] : '');

                    // 解析单元格类型
                    $type = 'n'; // 默认数字
                    if (preg_match('/t="([^"]*)"/', $attrs, $typeMatch)) {
                        $type = $typeMatch[1];
                    }

                    // 解析列号（从单元格引用如 A1, B2 中提取列）
                    $colIndex = 0;
                    if (preg_match('/r="([A-Z]+)\d+"/', $attrs, $refMatch)) {
                        $colIndex = $this->colLetterToIndex($refMatch[1]);
                    }

                    // 提取值
                    $value = '';
                    if (preg_match('/<v>(.*?)<\/v>/', $cellContent, $vMatch)) {
                        $value = $vMatch[1];
                    }

                    // 根据类型转换值
                    if ($type === 's') {
                        // 共享字符串索引
                        $idx = (int) $value;
                        $value = isset($sharedStrings[$idx]) ? $sharedStrings[$idx] : '';
                    } elseif ($type === 'b') {
                        // 布尔值
                        $value = $value === '1' ? 'TRUE' : 'FALSE';
                    } elseif ($type === 'e') {
                        // 错误
                        $value = '';
                    }
                    // 'n' (数字) 和 'str' (公式字符串) 直接使用 value
                    // 'inlineStr' 内联字符串
                    elseif ($type === 'inlineStr') {
                        if (preg_match('/<is>.*?<t[^>]*>(.*?)<\/t>.*?<\/is>/s', $cellContent, $isMatch)) {
                            $value = html_entity_decode($isMatch[1], ENT_QUOTES | ENT_XML1, 'UTF-8');
                        }
                    }

                    // 确保数组足够大
                    while (count($cells) <= $colIndex) {
                        $cells[] = '';
                    }
                    $cells[$colIndex] = $value;
                }
            }

            // 跳过完全为空的行（所有单元格都是空字符串）
            $isEmpty = true;
            foreach ($cells as $cell) {
                if ($cell !== '') {
                    $isEmpty = false;
                    break;
                }
            }
            if (!$isEmpty) {
                $rows[] = $cells;
            }
        }

        return $rows;
    }

    /**
     * 将列字母转为索引 (A=0, B=1, ..., Z=25, AA=26, ...)
     */
    private function colLetterToIndex($letters)
    {
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }
}
