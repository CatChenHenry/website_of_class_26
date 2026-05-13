<?php
class AdminController
{
	public function __construct()
	{
		if (!isset($_SESSION['username'])) {
			header("Location: /user/login");
			exit;
		}
		if (!isManager($_SESSION['permissions'])) {
			become403page();
		}
	}

	public function index()
	{
		$users = UserModel::getAllUsers();
		require ROOT_DIR . '/views/admin/index.php';
	}

	public function createUser()
	{
		require ROOT_DIR . '/views/admin/create-user.php';
	}

	public function storeUser()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			become404page();
		}
		verifyCsrfToken();
		$data = [
			'stu_no' => (int) ($_POST['stu_no'] ?? 0),
			'name' => trim($_POST['name'] ?? ''),
			'email' => trim($_POST['email'] ?? ''),
			'password' => trim($_POST['password'] ?? ''),
			'permissions' => trim($_POST['permissions'] ?? 'student'),
			'stu_class' => trim($_POST['stu_class'] ?? '8_26'),
			'score' => (int) ($_POST['score'] ?? 0)
		];
		if ($_POST['stu_no'] === '' || $_POST['stu_no'] === null || empty($data['name']) || empty($data['password'])) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '学号、姓名、密码不能为空！'];
			header("Location: /admin/createUser");
			exit;
		}
		if (strlen($data['password']) < 6) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '密码长度不能少于6位！'];
			header("Location: /admin/createUser");
			exit;
		}
		if (!in_array($data['permissions'], ['student', 'teacher', 'admin'])) {
			$data['permissions'] = 'student';
		}
		$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
		$result = UserModel::createUser($data);
		if ($result) {
			$_SESSION['flash_message'] = ['type' => 'success', 'text' => '新建用户成功！'];
			header("Location: /admin/index");
			exit;
		} else {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '新建用户失败！'];
			header("Location: /admin/index");
			exit;
		}
	}

	public function editUser()
	{
		$stuNo = (int) ($_GET['stu_no'] ?? 0);
		if (empty($stuNo)) {
			become404page();
		}
		$user = UserModel::getUserByStuNo($stuNo);
		if (!$user) {
			become404page();
		}
		if ($user['permissions'] === 'admin' || $user['permissions'] === 'administrator') {
			become403page();
		}
		$editUser = $user;
		require ROOT_DIR . '/views/admin/edit-user.php';
	}

	public function updateUser()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			become404page();
		}
		verifyCsrfToken();
		$stuNo = (int) ($_POST['stu_no'] ?? 0);
		if ($_POST['stu_no'] === '' || $_POST['stu_no'] === null) {
			become404page();
		}
		$targetUser = UserModel::getUserByStuNo($stuNo);
		if (!$targetUser) {
			become404page();
		}
		if ($targetUser['permissions'] === 'admin' || $targetUser['permissions'] === 'administrator') {
			become403page();
		}
		$data = [
			'name' => trim($_POST['name'] ?? ''),
			'email' => trim($_POST['email'] ?? ''),
			'permissions' => trim($_POST['permissions'] ?? 'student'),
			'stu_class' => trim($_POST['stu_class'] ?? '8_26'),
			'score' => (int) ($_POST['score'] ?? 0)
		];
		if (empty($data['name'])) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '姓名不能为空！'];
			header("Location: /admin/editUser?stu_no=" . $stuNo);
			exit;
		}
		if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '邮箱格式不正确！'];
			header("Location: /admin/editUser?stu_no=" . $stuNo);
			exit;
		}
		if (!in_array($data['permissions'], ['student', 'teacher', 'admin'])) {
			$data['permissions'] = 'student';
		}
		if ($data['permissions'] === 'admin') {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '不允许通过编辑操作将用户提升为管理员！'];
			header("Location: /admin/index");
			exit;
		}
		$result = UserModel::updateUser($stuNo, $data);
		if ($result) {
			$_SESSION['flash_message'] = ['type' => 'success', 'text' => '修改用户信息成功！'];
			header("Location: /admin/index");
			exit;
		} else {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '修改用户信息失败！'];
			header("Location: /admin/index");
			exit;
		}
	}

	public function deleteUser()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			become404page();
		}
		verifyCsrfToken();
		$stuNo = (int) ($_POST['stu_no'] ?? 0);
		if (empty($stuNo)) {
			become404page();
		}
		$user = UserModel::getUserByStuNo($stuNo);
		if (!$user) {
			become404page();
		}
		if ($user['permissions'] === 'admin' || $user['permissions'] === 'administrator') {
			become403page();
		}
		$oldAvatar = $user['avatar'] ?? '';
		$result = UserModel::deleteUser($stuNo);
		if ($result) {
			if (!empty($oldAvatar) && strpos($oldAvatar, '/static/avatars/') === 0) {
				$oldAvatarPath = ROOT_DIR . '/public' . $oldAvatar;
				$realBase = realpath(ROOT_DIR . '/public/static/avatars');
				$realPath = realpath($oldAvatarPath);
				if ($realBase && $realPath && str_starts_with($realPath, $realBase) && basename($realPath) !== 'default.jpg' && file_exists($realPath)) {
					@unlink($realPath);
				}
			}
			if (isAjaxRequest()) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true, 'message' => '删除用户成功']);
				exit;
			}
			$_SESSION['flash_message'] = ['type' => 'success', 'text' => '删除用户成功！'];
			header("Location: /admin/index");
			exit;
		} else {
			if (isAjaxRequest()) {
				header('Content-Type: application/json');
				echo json_encode(['success' => false, 'message' => '删除用户失败', 'csrf_token' => $_SESSION['csrf_token'] ?? '']);
				exit;
			}
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '删除用户失败！'];
			header("Location: /admin/index");
			exit;
		}
	}

	public function batchDeleteUsers()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			become404page();
		}
		verifyCsrfToken();

		$stuNos = $_POST['stu_nos'] ?? [];
		if (!is_array($stuNos) || empty($stuNos)) {
			if (isAjaxRequest()) {
				header('Content-Type: application/json');
				echo json_encode(['success' => false, 'message' => '未选择要删除的用户']);
				exit;
			}
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '未选择要删除的用户！'];
			header("Location: /admin/index");
			exit;
		}

		$deleted = 0;
		$failed = 0;

		foreach ($stuNos as $stuNo) {
			$stuNo = (int) $stuNo;
			if (empty($stuNo)) {
				$failed++;
				continue;
			}
			$user = UserModel::getUserByStuNo($stuNo);
			if (!$user || $user['permissions'] === 'admin' || $user['permissions'] === 'administrator') {
				$failed++;
				continue;
			}
			$oldAvatar = $user['avatar'] ?? '';
			$result = UserModel::deleteUser($stuNo);
			if ($result) {
				$deleted++;
				if (!empty($oldAvatar) && strpos($oldAvatar, '/static/avatars/') === 0) {
					$oldAvatarPath = ROOT_DIR . '/public' . $oldAvatar;
					$realBase = realpath(ROOT_DIR . '/public/static/avatars');
					$realPath = realpath($oldAvatarPath);
					if ($realBase && $realPath && str_starts_with($realPath, $realBase) && basename($realPath) !== 'default.jpg' && file_exists($realPath)) {
						@unlink($realPath);
					}
				}
			} else {
				$failed++;
			}
		}

		$message = "成功删除 {$deleted} 个用户";
		if ($failed > 0) {
			$message .= "，{$failed} 个失败（管理员或不存在）";
		}

		if (isAjaxRequest()) {
			header('Content-Type: application/json');
			echo json_encode(['success' => $deleted > 0, 'message' => $message, 'csrf_token' => $_SESSION['csrf_token'] ?? '']);
			exit;
		}
		$_SESSION['flash_message'] = ['type' => $deleted > 0 ? 'success' : 'error', 'text' => $message];
		header("Location: /admin/index");
		exit;
	}

	public function importUsers()
	{
		require ROOT_DIR . '/views/admin/import-users.php';
	}

	public function doImportUsers()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			become404page();
		}
		verifyCsrfToken();

		$isAjax = isAjaxRequest();
		$importResult = $this->processImport();

		if ($isAjax) {
			header('Content-Type: application/json');
			echo json_encode($importResult);
			exit;
		}
		require ROOT_DIR . '/views/admin/import-users.php';
	}

	private function processImport()
	{
		if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
			return [
				'success' => false,
				'total' => 0,
				'imported' => 0,
				'skipped' => 0,
				'errors' => ['文件上传失败，请重试']
			];
		}

		$tmpPath = $_FILES['excel_file']['tmp_name'];
		$originalName = $_FILES['excel_file']['name'];
		$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
		$rows = [];

		if ($ext === 'xlsx') {
			require_once ROOT_DIR . '/utils/SimpleXLSXReader.php';
			$result = SimpleXLSXReader::parse($tmpPath);
			if ($result === false) {
				return [
					'success' => false,
					'total' => 0,
					'imported' => 0,
					'skipped' => 0,
					'errors' => ['Excel 文件解析失败，请检查文件是否为有效的 .xlsx 格式']
				];
			}
			$rows = $result;
		} elseif ($ext === 'xls') {
			require_once ROOT_DIR . '/utils/SimpleXLSReader.php';
			$result = SimpleXLSReader::parse($tmpPath);
			if ($result === false) {
				return [
					'success' => false,
					'total' => 0,
					'imported' => 0,
					'skipped' => 0,
					'errors' => ['Excel 文件解析失败，请检查文件是否为有效的 .xls 格式']
				];
			}
			$rows = $result;
		} else {
			return [
				'success' => false,
				'total' => 0,
				'imported' => 0,
				'skipped' => 0,
				'errors' => ['仅支持 .xls 或 .xlsx 格式的文件']
			];
		}

		if (count($rows) < 2) {
			return [
				'success' => false,
				'total' => 0,
				'imported' => 0,
				'skipped' => 0,
				'errors' => ['文件中没有数据行（至少需要表头+1行数据）']
			];
		}

		$header = array_map('trim', $rows[0]);
		if (count($header) < 3 || $header[0] !== '学号' || $header[1] !== '姓名') {
			return [
				'success' => false,
				'total' => 0,
				'imported' => 0,
				'skipped' => 0,
				'errors' => ['表头格式不正确，第一行应以"学号,姓名,邮箱,密码,权限,班级,分数"开头，请下载模板查看正确格式']
			];
		}

		$colCount = 7;
		for ($i = 0; $i < count($rows); $i++) {
			while (count($rows[$i]) < $colCount) {
				$rows[$i][] = '';
			}
		}

		$filteredRows = [$rows[0]];
		for ($i = 1; $i < count($rows); $i++) {
			$allEmpty = true;
			for ($j = 0; $j < $colCount; $j++) {
				if (trim($rows[$i][$j]) !== '') {
					$allEmpty = false;
					break;
				}
			}
			if (!$allEmpty) {
				$filteredRows[] = $rows[$i];
			}
		}
		$rows = $filteredRows;

		$imported = 0;
		$skipped = 0;
		$errors = [];
		$totalDataRows = count($rows) - 1;

		for ($i = 1; $i < count($rows); $i++) {
			$row = $rows[$i];
			$rowNum = $i + 1;

			$stuNo = isset($row[0]) ? (int) trim($row[0]) : 0;
			$name = isset($row[1]) ? trim($row[1]) : '';
			$email = isset($row[2]) ? trim($row[2]) : '';
			$password = isset($row[3]) ? trim($row[3]) : '';
			$permissions = isset($row[4]) ? trim($row[4]) : 'student';
			$stuClass = isset($row[5]) ? trim($row[5]) : '8_26';
			$score = isset($row[6]) ? (int) trim($row[6]) : 0;

			$missing = [];
			if (empty($stuNo)) $missing[] = '学号';
			if (empty($name)) $missing[] = '姓名';
			if (empty($password)) $missing[] = '密码';
			if (!empty($missing)) {
				$errors[] = "第{$rowNum}行：" . implode('、', $missing) . "不能为空（原始数据: " . implode(',', array_slice($row, 0, 7)) . "）";
				$skipped++;
				continue;
			}

			if (strlen($password) < 6) {
				$errors[] = "第{$rowNum}行：密码长度不能少于6位";
				$skipped++;
				continue;
			}

			if (!in_array($permissions, ['student', 'admin'])) {
				$permissions = 'student';
			}

			$existingUser = UserModel::getUserByStuNo($stuNo);
			if ($existingUser) {
				$errors[] = "第{$rowNum}行：学号 {$stuNo} 已存在";
				$skipped++;
				continue;
			}

			$data = [
				'stu_no' => $stuNo,
				'name' => $name,
				'email' => $email,
				'password' => password_hash($password, PASSWORD_DEFAULT),
				'permissions' => $permissions,
				'stu_class' => $stuClass,
				'score' => $score
			];

			$result = UserModel::createUser($data);
			if ($result) {
				$imported++;
			} else {
				$errors[] = "第{$rowNum}行：数据库写入失败";
				$skipped++;
			}
		}

		return [
			'success' => $imported > 0,
			'total' => $totalDataRows,
			'imported' => $imported,
			'skipped' => $skipped,
			'errors' => $errors
		];
	}

	public function downloadTemplate()
	{
		$headers = ['学号', '姓名', '邮箱', '密码', '权限', '班级', '分数'];
		$example1 = ['2024001', '张三', 'zhangsan@example.com', '123456', 'student', '8_26', '0'];
		$example2 = ['2024002', '李四', '', 'abc123', 'student', '8_26', '0'];
		$xlsxContent = $this->generateSimpleXLSX([$headers, $example1, $example2]);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="import_users_template.xlsx"');
		header('Content-Length: ' . strlen($xlsxContent));
		header('Cache-Control: max-age=0');
		echo $xlsxContent;
		exit;
	}

	private function generateSimpleXLSX(array $rows)
	{
		$sharedStrings = [];
		$siIndex = [];
		$sheetData = '';

		foreach ($rows as $r => $row) {
			$rowNum = $r + 1;
			$sheetData .= '<row r="' . $rowNum . '">';
			for ($c = 0; $c < count($row); $c++) {
				$colLetter = $this->indexToColLetter($c);
				$cellRef = $colLetter . $rowNum;
				$val = (string) $row[$c];

				if ($val === '') {
					$sheetData .= '<c r="' . $cellRef . '"/>';
					continue;
				}

				if (is_numeric($val) && strlen($val) > 0 && $val[0] !== '0') {
					$sheetData .= '<c r="' . $cellRef . '"><v>' . htmlspecialchars($val) . '</v></c>';
				} else {
					if (!isset($siIndex[$val])) {
						$siIndex[$val] = count($sharedStrings);
						$sharedStrings[] = $val;
					}
					$sheetData .= '<c r="' . $cellRef . '" t="s"><v>' . $siIndex[$val] . '</v></c>';
				}
			}
			$sheetData .= '</row>';
		}

		$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
			'<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($siIndex) . '" uniqueCount="' . count($sharedStrings) . '">';
		foreach ($sharedStrings as $s) {
			$ssXml .= '<si><t>' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
		}
		$ssXml .= '</sst>';

		$sheet1Xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
			'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
			'<sheetData>' . $sheetData . '</sheetData></worksheet>';

		return $this->buildXLSXZip($ssXml, $sheet1Xml);
	}

	private function indexToColLetter(int $index)
	{
		$letter = '';
		$index++;
		while ($index > 0) {
			$index--;
			$letter = chr(ord('A') + ($index % 26)) . $letter;
			$index = (int) ($index / 26);
		}
		return $letter;
	}

	private function buildXLSXZip(string $sharedStringsXml, string $sheet1Xml)
	{
		$Content_Types = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
			'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
			'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
			'<Default Extension="xml" ContentType="application/xml"/>' .
			'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
			'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
			'<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>' .
			'</Types>';

		$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
			'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
			'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
			'</Relationships>';

		$workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
			'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
			'<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>';

		$workbook_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
			'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
			'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
			'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>' .
			'</Relationships>';

		$files = [
			'[Content_Types].xml' => $Content_Types,
			'_rels/.rels' => $rels,
			'xl/workbook.xml' => $workbook,
			'xl/_rels/workbook.xml.rels' => $workbook_rels,
			'xl/worksheets/sheet1.xml' => $sheet1Xml,
			'xl/sharedStrings.xml' => $sharedStringsXml,
		];

		return $this->createZipInMemory($files);
	}

	private function createZipInMemory(array $files)
	{
		$zipData = '';
		$centralDir = '';
		$offset = 0;

		foreach ($files as $name => $content) {
			$nameBytes = '';
			for ($i = 0; $i < strlen($name); $i++) {
				$nameBytes .= $name[$i];
			}

			$compressed = gzdeflate($content);
			$crc = crc32($content);
			$size = strlen($content);
			$cSize = strlen($compressed);
			$nameLen = strlen($nameBytes);

			$localHeader = "PK\x03\x04";
			$localHeader .= pack('v', 20);
			$localHeader .= pack('v', 0);
			$localHeader .= pack('v', 8);
			$localHeader .= pack('v', 0);
			$localHeader .= pack('v', 0);
			$localHeader .= pack('V', $crc);
			$localHeader .= pack('V', $cSize);
			$localHeader .= pack('V', $size);
			$localHeader .= pack('v', $nameLen);
			$localHeader .= pack('v', 0);
			$localHeader .= $nameBytes;
			$localHeader .= $compressed;

			$zipData .= $localHeader;

			$centEntry = "PK\x01\x02";
			$centEntry .= pack('v', 20);
			$centEntry .= pack('v', 20);
			$centEntry .= pack('v', 0);
			$centEntry .= pack('v', 8);
			$centEntry .= pack('v', 0);
			$centEntry .= pack('v', 0);
			$centEntry .= pack('V', $crc);
			$centEntry .= pack('V', $cSize);
			$centEntry .= pack('V', $size);
			$centEntry .= pack('v', $nameLen);
			$centEntry .= pack('v', 0);
			$centEntry .= pack('v', 0);
			$centEntry .= pack('v', 0);
			$centEntry .= pack('v', 0);
			$centEntry .= pack('V', 0);
			$centEntry .= pack('V', $offset);
			$centEntry .= $nameBytes;

			$centralDir .= $centEntry;
			$offset += strlen($localHeader);
		}

		$centralDirOffset = $offset;
		$centralDirSize = strlen($centralDir);

		$endRecord = "PK\x05\x06";
		$endRecord .= pack('v', 0);
		$endRecord .= pack('v', 0);
		$endRecord .= pack('v', count($files));
		$endRecord .= pack('v', count($files));
		$endRecord .= pack('V', $centralDirSize);
		$endRecord .= pack('V', $centralDirOffset);
		$endRecord .= pack('v', 0);

		return $zipData . $centralDir . $endRecord;
	}
}
