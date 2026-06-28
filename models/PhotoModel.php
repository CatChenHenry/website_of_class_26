<?php
require_once ROOT_DIR . '/utils/DB.php';

class PhotoModel
{
    // 获取相册的所有照片
    public static function getPhotosByAlbum($albumId, $limit = 50, $offset = 0)
    {
        $albumId = (int) $albumId;
        $limit = (int) $limit;
        $offset = (int) $offset;
        
        $sql = "SELECT * FROM photos WHERE album_id = ? ORDER BY sort_order ASC, created_at ASC LIMIT ? OFFSET ?";
        $stmt = DB::query($sql, [$albumId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 获取用户的最近照片
    public static function getRecentPhotosByUser($stuNo, $limit = 12)
    {
        $stuNo = (int) $stuNo;
        $limit = (int) $limit;
        
        $sql = "SELECT * FROM photos WHERE stu_no = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = DB::query($sql, [$stuNo, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 创建照片
    public static function createPhoto($albumId, $stuNo, $imageUrl, $thumbnailUrl = null, $description = null)
    {
        $albumId = (int) $albumId;
        $stuNo = (int) $stuNo;
        
        // 自动设置排序值为当前最大值+1
        $maxOrder = (int)(DB::query("SELECT COALESCE(MAX(sort_order), -1) as m FROM photos WHERE album_id = ?", [$albumId])->fetch(PDO::FETCH_ASSOC)['m'] ?? -1);
        $sortOrder = $maxOrder + 1;
        
        $sql = "INSERT INTO photos (album_id, stu_no, image_url, thumbnail_url, description, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?)";
        DB::query($sql, [$albumId, $stuNo, $imageUrl, $thumbnailUrl, $description, $sortOrder]);
        
        $photoId = DB::lastInsertId();
        if ($photoId && $photoId > 0) {
            // 更新相册照片数量
            $count = self::getPhotoCountByAlbum($albumId);
            AlbumModel::updatePhotoCount($albumId, $count);
            return (int) $photoId;
        }
        
        return false;
    }
    
    // 删除照片
    public static function deletePhoto($photoId, $stuNo = null)
    {
        $photoId = (int) $photoId;
        
        // 先获取照片信息以更新相册计数
        $photo = self::getPhotoById($photoId);
        if (!$photo) return false;
        
        if ($stuNo !== null) {
            $stuNo = (int) $stuNo;
            $sql = "DELETE FROM photos WHERE id = ? AND stu_no = ?";
            $stmt = DB::query($sql, [$photoId, $stuNo]);
        } else {
            $sql = "DELETE FROM photos WHERE id = ?";
            $stmt = DB::query($sql, [$photoId]);
        }
        
        if ($stmt->rowCount() > 0) {
            // 更新相册照片数量
            $count = self::getPhotoCountByAlbum($photo['album_id']);
            AlbumModel::updatePhotoCount($photo['album_id'], $count);
            return true;
        }
        
        return false;
    }
    
    // 获取照片详情
    public static function getPhotoById($photoId)
    {
        $photoId = (int) $photoId;
        
        $sql = "SELECT * FROM photos WHERE id = ?";
        $stmt = DB::query($sql, [$photoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 点赞照片
    public static function likePhoto($photoId)
    {
        $photoId = (int) $photoId;
        
        $sql = "UPDATE photos SET like_count = like_count + 1 WHERE id = ?";
        $stmt = DB::query($sql, [$photoId]);
        return $stmt->rowCount() > 0;
    }
    
    // 取消点赞照片
    public static function unlikePhoto($photoId)
    {
        $photoId = (int) $photoId;
        
        $sql = "UPDATE photos SET like_count = GREATEST(like_count - 1, 0) WHERE id = ?";
        $stmt = DB::query($sql, [$photoId]);
        return $stmt->rowCount() > 0;
    }
    
    // 获取相册照片数量
    public static function getPhotoCountByAlbum($albumId)
    {
        $albumId = (int) $albumId;
        
        $sql = "SELECT COUNT(*) as count FROM photos WHERE album_id = ?";
        $stmt = DB::query($sql, [$albumId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    // 获取用户照片数量
    public static function getPhotoCountByUser($stuNo)
    {
        $stuNo = (int) $stuNo;
        
        $sql = "SELECT COUNT(*) as count FROM photos WHERE stu_no = ?";
        $stmt = DB::query($sql, [$stuNo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    // 更新照片描述
    public static function updatePhotoDescription($photoId, $description)
    {
        $photoId = (int) $photoId;
        
        $sql = "UPDATE photos SET description = ? WHERE id = ?";
        $stmt = DB::query($sql, [$description, $photoId]);
        return $stmt->rowCount() > 0;
    }

    // 批量更新照片排序
    public static function batchUpdateOrder(array $orderedIds)
    {
        $sql = "UPDATE photos SET sort_order = ? WHERE id = ?";
        foreach ($orderedIds as $index => $photoId) {
            DB::query($sql, [$index, (int)$photoId]);
        }
        return true;
    }

    // 获取相册第一张照片（用于默认封面）
    public static function getFirstPhotoByAlbum($albumId)
    {
        $albumId = (int) $albumId;
        $sql = "SELECT * FROM photos WHERE album_id = ? ORDER BY sort_order ASC, created_at ASC LIMIT 1";
        $stmt = DB::query($sql, [$albumId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}