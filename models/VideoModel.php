<?php
require_once ROOT_DIR . '/utils/DB.php';

class VideoModel
{
    public static function getAll($orderBy = 'episode', $orderDir = 'ASC')
    {
        $cols = ['episode', 'created_at'];
        if (!in_array($orderBy, $cols)) $orderBy = 'episode';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM videos ORDER BY $orderBy $orderDir";
        return DB::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $sql = "SELECT * FROM videos WHERE id = ?";
        $stmt = DB::query($sql, [(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($episode, $title, $videoUrl, $description = null, $thumbnail = null)
    {
        $sql = "INSERT INTO videos (episode, title, video_url, description, thumbnail) VALUES (?, ?, ?, ?, ?)";
        $stmt = DB::query($sql, [(int)$episode, $title, $videoUrl, $description, $thumbnail]);
        return $stmt->rowCount() > 0 ? DB::lastInsertId() : false;
    }

    public static function update($id, $episode, $title, $videoUrl, $description = null, $thumbnail = null)
    {
        $sql = "UPDATE videos SET episode=?, title=?, video_url=?, description=?, thumbnail=? WHERE id=?";
        $stmt = DB::query($sql, [(int)$episode, $title, $videoUrl, $description, $thumbnail, (int)$id]);
        return $stmt->rowCount() >= 0;
    }

    public static function delete($id)
    {
        $sql = "DELETE FROM videos WHERE id = ?";
        $stmt = DB::query($sql, [(int)$id]);
        return $stmt->rowCount() > 0;
    }

    public static function getCount()
    {
        $sql = "SELECT COUNT(*) as cnt FROM videos";
        $r = DB::query($sql)->fetch(PDO::FETCH_ASSOC);
        return (int)($r['cnt'] ?? 0);
    }
}
