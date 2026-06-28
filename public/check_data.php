<?php
// 临时数据检查脚本
define('ROOT_DIR', dirname(__DIR__));

$envFile = ROOT_DIR . '/.env';
if (file_exists($envFile)) {
	$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || str_starts_with($line, '#')) continue;
		if (strpos($line, '=') === false) continue;
		$key = trim(explode('=', $line, 2)[0]);
		if (str_starts_with($key, 'DB_')) {
			putenv($line);
		}
	}
}

require_once ROOT_DIR . '/utils/functions.php';
require_once ROOT_DIR . '/utils/DB.php';
require_once ROOT_DIR . '/models/UserModel.php';
require_once ROOT_DIR . '/models/PostModel.php';
require_once ROOT_DIR . '/models/GuestbookModel.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== 数据检查脚本 ===\n\n";

// 检查当前会话
session_start();
echo "会话信息:\n";
echo "SESSION ID: " . ($_SESSION['id'] ?? '未设置') . "\n";
echo "SESSION username: " . ($_SESSION['username'] ?? '未设置') . "\n";
echo "SESSION stu_no: " . ($_SESSION['stu_no'] ?? '未设置') . "\n\n";

// 检查 posts 表
echo "=== posts 表检查 ===\n";
try {
    $sql = "SELECT COUNT(*) as count FROM posts WHERE stu_no = -1";
    $stmt = DB::query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "学号 -1 的说说数量: " . ($result['count'] ?? 0) . "\n";
    
    $sql = "SELECT id, stu_no, username, content, created_at FROM posts WHERE stu_no = -1 ORDER BY created_at DESC LIMIT 5";
    $stmt = DB::query($sql);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "最近的说说:\n";
    if (empty($posts)) {
        echo "  无记录\n";
    } else {
        foreach ($posts as $post) {
            echo "  ID: " . $post['id'] . ", 内容: " . mb_substr($post['content'], 0, 50) . "...\n";
        }
    }
} catch (Exception $e) {
    echo "查询失败: " . $e->getMessage() . "\n";
}

echo "\n=== guestbook 表检查 ===\n";
try {
    $sql = "SELECT COUNT(*) as count FROM guestbook WHERE target_stu_no = -1";
    $stmt = DB::query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "目标学号 -1 的留言数量: " . ($result['count'] ?? 0) . "\n";
    
    $sql = "SELECT id, target_stu_no, visitor_stu_no, visitor_name, message, created_at FROM guestbook WHERE target_stu_no = -1 ORDER BY created_at DESC LIMIT 5";
    $stmt = DB::query($sql);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "最近的留言:\n";
    if (empty($messages)) {
        echo "  无记录\n";
    } else {
        foreach ($messages as $msg) {
            echo "  ID: " . $msg['id'] . ", 访客: " . $msg['visitor_name'] . " (" . $msg['visitor_stu_no'] . "), 内容: " . mb_substr($msg['message'], 0, 50) . "...\n";
        }
    }
} catch (Exception $e) {
    echo "查询失败: " . $e->getMessage() . "\n";
}

echo "\n=== 通过模型查询 ===\n";
try {
    $posts = PostModel::getPostsByUser(-1, 5, 0);
    echo "PostModel::getPostsByUser(-1) 返回记录数: " . count($posts) . "\n";
    
    $messages = GuestbookModel::getMessagesByUser(-1, 5, 0);
    echo "GuestbookModel::getMessagesByUser(-1) 返回记录数: " . count($messages) . "\n";
} catch (Exception $e) {
    echo "模型查询失败: " . $e->getMessage() . "\n";
}

echo "\n=== 检查完成 ===\n";