<?php
require_once ROOT_DIR . '/utils/DB.php';

class AlbumModel
{
    // 获取用户的所有相册
    public static function getAlbumsByUser($stuNo, $limit = 10, $offset = 0)
    {
        $stuNo = (int) $stuNo;
        $limit = (int) $limit;
        $offset = (int) $offset;
        
        $sql = "SELECT * FROM albums WHERE stu_no = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = DB::query($sql, [$stuNo, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 创建相册
    public static function createAlbum($stuNo, $albumName, $description = null, $coverImage = null)
    {
        $stuNo = (int) $stuNo;
        
        $sql = "INSERT INTO albums (stu_no, album_name, description, cover_image) 
                VALUES (?, ?, ?, ?)";
        $stmt = DB::query($sql, [$stuNo, $albumName, $description, $coverImage]);
        return $stmt->rowCount() > 0 ? DB::lastInsertId() : false;
    }
    
    // 更新相册
    public static function updateAlbum($albumId, $albumName, $description = null, $coverImage = null)
    {
        $albumId = (int) $albumId;
        
        $sql = "UPDATE albums SET album_name = ?, description = ?, cover_image = ? WHERE id = ?";
        $stmt = DB::query($sql, [$albumName, $description, $coverImage, $albumId]);
        return $stmt->rowCount() > 0;
    }
    
    // 删除相册
    public static function deleteAlbum($albumId, $stuNo = null)
    {
        $albumId = (int) $albumId;
        
        if ($stuNo !== null) {
            $stuNo = (int) $stuNo;
            $sql = "DELETE FROM albums WHERE id = ? AND stu_no = ?";
            $stmt = DB::query($sql, [$albumId, $stuNo]);
        } else {
            $sql = "DELETE FROM albums WHERE id = ?";
            $stmt = DB::query($sql, [$albumId]);
        }
        
        return $stmt->rowCount() > 0;
    }
    
    // 获取相册详情
    public static function getAlbumById($albumId)
    {
        $albumId = (int) $albumId;
        
        $sql = "SELECT * FROM albums WHERE id = ?";
        $stmt = DB::query($sql, [$albumId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 更新相册照片数量
    public static function updatePhotoCount($albumId, $count)
    {
        $albumId = (int) $albumId;
        $count = (int) $count;
        
        $sql = "UPDATE albums SET photo_count = ? WHERE id = ?";
        $stmt = DB::query($sql, [$count, $albumId]);
        return $stmt->rowCount() > 0;
    }
    
    // 获取相册数量
    public static function getAlbumCountByUser($stuNo)
    {
        $stuNo = (int) $stuNo;
        
        $sql = "SELECT COUNT(*) as count FROM albums WHERE stu_no = ?";
        $stmt = DB::query($sql, [$stuNo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // 单独设置封面图
    public static function setCoverImage($albumId, $imageUrl)
    {
        $albumId = (int) $albumId;
        $sql = "UPDATE albums SET cover_image = ? WHERE id = ?";
        $stmt = DB::query($sql, [$imageUrl, $albumId]);
        return $stmt->rowCount() > 0;
    }
}