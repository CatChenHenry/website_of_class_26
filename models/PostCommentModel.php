<?php
require_once ROOT_DIR . '/utils/DB.php';
require_once ROOT_DIR . '/models/UserModel.php';

class PostCommentModel
{
    // 获取说说的所有评论
    public static function getCommentsByPost($postId, $limit = 50, $offset = 0)
    {
        $postId = (int) $postId;
        $limit = (int) $limit;
        $offset = (int) $offset;
        
        $sql = "SELECT pc.*, u.avatar as user_avatar 
                FROM post_comments pc 
                LEFT JOIN users u ON pc.stu_no = u.stu_no 
                WHERE pc.post_id = ? 
                ORDER BY pc.created_at ASC 
                LIMIT ? OFFSET ?";
        $stmt = DB::query($sql, [$postId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 创建评论
    public static function createComment($postId, $stuNo, $username, $content, $avatar = null)
    {
        $postId = (int) $postId;
        $stuNo = (int) $stuNo;
        
        if (empty($avatar)) {
            try {
                $user = UserModel::getUserByStuNo($stuNo);
                $avatar = $user['avatar'] ?? '/static/avatars/default.jpg';
            } catch (Exception $e) {
                error_log('ERROR PostCommentModel::createComment: getUserByStuNo failed - ' . $e->getMessage());
                $avatar = '/static/avatars/default.jpg';
            }
        }
        
        $sql = "INSERT INTO post_comments (post_id, stu_no, username, avatar, content) 
                VALUES (?, ?, ?, ?, ?)";
        try {
            $stmt = DB::query($sql, [$postId, $stuNo, $username, $avatar, $content]);
            
            if ($stmt->rowCount() > 0) {
                $commentId = DB::lastInsertId();
                if ($commentId && $commentId > 0) {
                    self::incrementCommentCount($postId);
                    return $commentId;
                } else {
                    error_log('ERROR PostCommentModel::createComment: lastInsertId returned invalid ID: ' . $commentId . ' for postId=' . $postId);
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log('ERROR PostCommentModel::createComment: SQL failed - ' . $e->getMessage() . ', SQL=' . $sql);
            return false;
        }
    }
    
    // 删除评论
    public static function deleteComment($commentId, $stuNo = null)
    {
        $commentId = (int) $commentId;
        
        // 先获取评论信息以更新计数
        $comment = self::getCommentById($commentId);
        if (!$comment) return false;
        
        if ($stuNo !== null) {
            $stuNo = (int) $stuNo;
            $sql = "DELETE FROM post_comments WHERE id = ? AND stu_no = ?";
            $stmt = DB::query($sql, [$commentId, $stuNo]);
        } else {
            $sql = "DELETE FROM post_comments WHERE id = ?";
            $stmt = DB::query($sql, [$commentId]);
        }
        
        if ($stmt->rowCount() > 0) {
            // 更新说说的评论计数
            self::decrementCommentCount($comment['post_id']);
            return true;
        }
        
        return false;
    }
    
    // 获取评论详情
    public static function getCommentById($commentId)
    {
        $commentId = (int) $commentId;
        
        $sql = "SELECT * FROM post_comments WHERE id = ?";
        $stmt = DB::query($sql, [$commentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 增加评论计数
    public static function incrementCommentCount($postId)
    {
        $postId = (int) $postId;
        
        $sql = "UPDATE posts SET comment_count = comment_count + 1 WHERE id = ?";
        $stmt = DB::query($sql, [$postId]);
        return $stmt->rowCount() > 0;
    }
    
    // 减少评论计数
    public static function decrementCommentCount($postId)
    {
        $postId = (int) $postId;
        
        $sql = "UPDATE posts SET comment_count = GREATEST(comment_count - 1, 0) WHERE id = ?";
        $stmt = DB::query($sql, [$postId]);
        return $stmt->rowCount() > 0;
    }
    
    // 获取评论数量
    public static function getCommentCountByPost($postId)
    {
        $postId = (int) $postId;
        
        $sql = "SELECT COUNT(*) as count FROM post_comments WHERE post_id = ?";
        $stmt = DB::query($sql, [$postId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}