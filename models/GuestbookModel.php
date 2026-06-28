<?php
require_once ROOT_DIR . '/utils/DB.php';
require_once ROOT_DIR . '/models/UserModel.php';

class GuestbookModel
{
    // 获取用户的留言
    public static function getMessagesByUser($targetStuNo, $limit = 20, $offset = 0)
    {
        $targetStuNo = (int) $targetStuNo;
        $limit = (int) $limit;
        $offset = (int) $offset;
        
        $sql = "SELECT g.*, u.avatar as visitor_avatar 
                FROM guestbook g 
                LEFT JOIN users u ON g.visitor_stu_no = u.stu_no 
                WHERE g.target_stu_no = ? 
                ORDER BY g.created_at DESC 
                LIMIT ? OFFSET ?";
        $stmt = DB::query($sql, [$targetStuNo, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 创建留言
    public static function createMessage($targetStuNo, $visitorStuNo, $visitorName, $message)
    {
        $targetStuNo = (int) $targetStuNo;
        $visitorStuNo = (int) $visitorStuNo;
        
        // 获取访客头像
        $visitor = UserModel::getUserByStuNo($visitorStuNo);
        $visitorAvatar = $visitor['avatar'] ?? '/static/avatars/default.jpg';
        
        $sql = "INSERT INTO guestbook (target_stu_no, visitor_stu_no, visitor_name, visitor_avatar, message) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = DB::query($sql, [$targetStuNo, $visitorStuNo, $visitorName, $visitorAvatar, $message]);
        return $stmt->rowCount() > 0 ? DB::lastInsertId() : false;
    }
    
    // 删除留言（只有留言主人、目标用户或管理员可以删除）
    public static function deleteMessage($messageId, $userId, $isAdmin = false)
    {
        $messageId = (int) $messageId;
        $userId = (int) $userId;
        
        if ($isAdmin) {
            $sql = "DELETE FROM guestbook WHERE id = ?";
            $stmt = DB::query($sql, [$messageId]);
        } else {
            $sql = "DELETE FROM guestbook WHERE id = ? AND (target_stu_no = ? OR visitor_stu_no = ?)";
            $stmt = DB::query($sql, [$messageId, $userId, $userId]);
        }
        return $stmt->rowCount() > 0;
    }
    
    // 获取留言数量
    public static function getMessageCountByUser($targetStuNo)
    {
        $targetStuNo = (int) $targetStuNo;
        
        $sql = "SELECT COUNT(*) as count FROM guestbook WHERE target_stu_no = ?";
        $stmt = DB::query($sql, [$targetStuNo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    // 获取最新留言
    public static function getLatestMessages($targetStuNo, $limit = 5)
    {
        $targetStuNo = (int) $targetStuNo;
        $limit = (int) $limit;
        
        $sql = "SELECT g.*, u.avatar as visitor_avatar 
                FROM guestbook g 
                LEFT JOIN users u ON g.visitor_stu_no = u.stu_no 
                WHERE g.target_stu_no = ? 
                ORDER BY g.created_at DESC 
                LIMIT ?";
        $stmt = DB::query($sql, [$targetStuNo, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}