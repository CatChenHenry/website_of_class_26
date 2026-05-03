<?php
class DB
{
    private static $pdo = null;

    public static function connect()
    {
        if (self::$pdo === null) {
            $config = require ROOT_DIR . '/config/database.php';
            try {
                self::$pdo = new PDO(
                    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                error_log("数据库连接失败：" . $e->getMessage());
                die("数据库连接失败，请稍后重试");
            }
        }
        return self::$pdo;
    }

    public static function query($sql, $params = [])
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
