<?php
// 数据库表结构检查脚本
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

header('Content-Type: text/plain; charset=utf-8');

echo "=== 数据库表结构检查脚本 ===\n\n";

try {
    // 检查 posts 表
    echo "=== posts 表结构 ===\n";
    $sql = "SHOW COLUMNS FROM posts";
    $stmt = DB::query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredColumns = ['id', 'stu_no', 'username', 'content', 'like_count', 'comment_count', 'share_count', 'created_at'];
    $foundColumns = [];
    
    foreach ($columns as $col) {
        echo "  " . $col['Field'] . " : " . $col['Type'] . " (" . $col['Null'] . ")\n";
        $foundColumns[] = $col['Field'];
    }
    
    echo "\n缺失的列:\n";
    $missing = array_diff($requiredColumns, $foundColumns);
    if (empty($missing)) {
        echo "  无\n";
    } else {
        foreach ($missing as $col) {
            echo "  " . $col . "\n";
        }
    }
    
    // 检查 post_comments 表
    echo "\n=== post_comments 表结构 ===\n";
    try {
        $sql = "SHOW COLUMNS FROM post_comments";
        $stmt = DB::query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $requiredColumns = ['id', 'post_id', 'stu_no', 'username', 'content', 'created_at'];
        $foundColumns = [];
        
        if (empty($columns)) {
            echo "  表不存在或为空\n";
        } else {
            foreach ($columns as $col) {
                echo "  " . $col['Field'] . " : " . $col['Type'] . " (" . $col['Null'] . ")\n";
                $foundColumns[] = $col['Field'];
            }
            
            echo "\n缺失的列:\n";
            $missing = array_diff($requiredColumns, $foundColumns);
            if (empty($missing)) {
                echo "  无\n";
            } else {
                foreach ($missing as $col) {
                    echo "  " . $col . "\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "  错误: " . $e->getMessage() . "\n";
        echo "  post_comments表可能不存在\n";
    }
    
    // 检查表是否存在
    echo "\n=== 表存在性检查 ===\n";
    $tables = ['posts', 'post_comments', 'guestbook', 'users'];
    foreach ($tables as $table) {
        try {
            $sql = "SELECT 1 FROM $table LIMIT 1";
            DB::query($sql);
            echo "  $table: 存在\n";
        } catch (Exception $e) {
            echo "  $table: 不存在 (" . $e->getMessage() . ")\n";
        }
    }
    
    // 检查数据示例
    echo "\n=== 数据示例检查 ===\n";
    try {
        $sql = "SELECT id, stu_no, username, content, like_count, comment_count FROM posts WHERE stu_no = -1 LIMIT 1";
        $stmt = DB::query($sql);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($post) {
            echo "  找到说说示例: ID=" . $post['id'] . ", 学号=" . $post['stu_no'] . ", 用户=" . $post['username'] . "\n";
            echo "  点赞数=" . ($post['like_count'] ?? 'null') . ", 评论数=" . ($post['comment_count'] ?? 'null') . "\n";
        } else {
            echo "  无学号为-1的说说\n";
        }
    } catch (Exception $e) {
        echo "  查询失败: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "数据库连接失败: " . $e->getMessage() . "\n";
}

echo "\n=== 检查完成 ===\n";