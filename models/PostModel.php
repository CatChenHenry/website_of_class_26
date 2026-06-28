<?php
require_once ROOT_DIR . '/utils/DB.php';
require_once ROOT_DIR . '/models/UserModel.php';

class PostModel
{
    // 获取用户的所有说说
    public static function getPostsByUser($stuNo, $limit = 20, $offset = 0)
    {
        $stuNo = (int) $stuNo;
        $limit = (int) $limit;
        $offset = (int) $offset;
        
        $sql = "SELECT p.*, u.avatar as user_avatar 
                FROM posts p 
                LEFT JOIN users u ON p.stu_no = u.stu_no 
                WHERE p.stu_no = ? 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        $stmt = DB::query($sql, [$stuNo, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 获取所有好友的说说（时间线）
    public static function getTimelinePosts($stuNo, $limit = 20, $offset = 0)
    {
        $stuNo = (int) $stuNo;
        $limit = (int) $limit;
        $offset = (int) $offset;
        
        // 这里可以扩展为获取好友的说说，目前先获取所有说说
        $sql = "SELECT p.*, u.avatar as user_avatar 
                FROM posts p 
                LEFT JOIN users u ON p.stu_no = u.stu_no 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        $stmt = DB::query($sql, [$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 创建说说
    public static function createPost($stuNo, $username, $content, $images = null, $location = null)
    {
        $stuNo = (int) $stuNo;
        
        // 获取用户头像
        $user = UserModel::getUserByStuNo($stuNo);
        $avatar = $user['avatar'] ?? '/static/avatars/default.jpg';
        
        $sql = "INSERT INTO posts (stu_no, username, avatar, content, images, location) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = DB::query($sql, [$stuNo, $username, $avatar, $content, $images, $location]);
        return $stmt->rowCount() > 0 ? DB::lastInsertId() : false;
    }
    
    // 更新说说
    public static function updatePost($postId, $content, $images = null, $location = null)
    {
        $postId = (int) $postId;
        
        $sql = "UPDATE posts SET content = ?, images = ?, location = ? WHERE id = ?";
        $stmt = DB::query($sql, [$content, $images, $location, $postId]);
        return $stmt->rowCount() > 0;
    }
    
    // 删除说说
    public static function deletePost($postId, $stuNo = null)
    {
        $postId = (int) $postId;
        
        if ($stuNo !== null) {
            $stuNo = (int) $stuNo;
            $sql = "DELETE FROM posts WHERE id = ? AND stu_no = ?";
            $stmt = DB::query($sql, [$postId, $stuNo]);
        } else {
            $sql = "DELETE FROM posts WHERE id = ?";
            $stmt = DB::query($sql, [$postId]);
        }
        
        return $stmt->rowCount() > 0;
    }
    
    // 检查用户是否已点赞
    public static function hasLiked($postId, $stuNo)
    {
        $postId = (int) $postId;
        $stuNo = (int) $stuNo;
        
        $sql = "SELECT 1 FROM post_likes WHERE post_id = ? AND stu_no = ? LIMIT 1";
        $stmt = DB::query($sql, [$postId, $stuNo]);
        return $stmt->rowCount() > 0;
    }
    
    // 点赞说说（防重复）
    public static function likePost($postId, $stuNo = null, $username = null)
    {
        $postId = (int) $postId;
        
        // 如果提供了stuNo，检查是否已点赞
        if ($stuNo !== null) {
            $stuNo = (int) $stuNo;
            if (self::hasLiked($postId, $stuNo)) {
                return false; // 已点赞，不再重复点赞
            }
            
            // 插入点赞记录
            $sql = "INSERT INTO post_likes (post_id, stu_no, username) VALUES (?, ?, ?)";
            try {
                $stmt = DB::query($sql, [$postId, $stuNo, $username ?? '']);
            } catch (Exception $e) {
                // 如果违反唯一约束（重复点赞），返回false
                return false;
            }
        }
        
        // 更新点赞计数
        $sql = "UPDATE posts SET like_count = like_count + 1 WHERE id = ?";
        $stmt = DB::query($sql, [$postId]);
        return $stmt->rowCount() > 0;
    }
    
    // 取消点赞说说
    public static function unlikePost($postId, $stuNo = null)
    {
        $postId = (int) $postId;
        
        // 如果提供了stuNo，删除点赞记录
        if ($stuNo !== null) {
            $stuNo = (int) $stuNo;
            $sql = "DELETE FROM post_likes WHERE post_id = ? AND stu_no = ?";
            DB::query($sql, [$postId, $stuNo]);
        }
        
        $sql = "UPDATE posts SET like_count = GREATEST(like_count - 1, 0) WHERE id = ?";
        $stmt = DB::query($sql, [$postId]);
        return $stmt->rowCount() > 0;
    }
    
    // 增加评论数
    public static function incrementCommentCount($postId)
    {
        $postId = (int) $postId;
        
        $sql = "UPDATE posts SET comment_count = comment_count + 1 WHERE id = ?";
        $stmt = DB::query($sql, [$postId]);
        return $stmt->rowCount() > 0;
    }
    
    // 减少评论数
    public static function decrementCommentCount($postId)
    {
        $postId = (int) $postId;
        
        $sql = "UPDATE posts SET comment_count = GREATEST(comment_count - 1, 0) WHERE id = ?";
        $stmt = DB::query($sql, [$postId]);
        return $stmt->rowCount() > 0;
    }
    
    // 获取说说详情
    public static function getPostById($postId)
    {
        $postId = (int) $postId;
        
        $sql = "SELECT p.*, u.avatar as user_avatar 
                FROM posts p 
                LEFT JOIN users u ON p.stu_no = u.stu_no 
                WHERE p.id = ?";
        $stmt = DB::query($sql, [$postId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 获取说说数量
    public static function getPostCountByUser($stuNo)
    {
        $stuNo = (int) $stuNo;
        
        $sql = "SELECT COUNT(*) as count FROM posts WHERE stu_no = ?";
        $stmt = DB::query($sql, [$stuNo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}