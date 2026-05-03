<?php
require_once ROOT_DIR . '/utils/DB.php';

class ActivityModel
{
	public static function getAllActivities()
	{
		$sql = "SELECT id, name, activity_time, content, created_at FROM activities ORDER BY activity_time DESC";
		$stmt = DB::query($sql);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function getActivityById($id)
	{
		$id = (int) $id;
		$sql = "SELECT id, name, activity_time, content, created_at FROM activities WHERE id = ?";
		$stmt = DB::query($sql, [$id]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public static function createActivity($data)
	{
		$sql = "INSERT INTO activities (name, activity_time, content) VALUES (?, ?, ?)";
		$stmt = DB::query($sql, [$data['name'], $data['activity_time'], $data['content'] ?? '']);
		return $stmt->rowCount() > 0;
	}

	public static function updateActivity($id, $data)
	{
		$id = (int) $id;
		$sql = "UPDATE activities SET name = ?, activity_time = ?, content = ? WHERE id = ?";
		$stmt = DB::query($sql, [$data['name'], $data['activity_time'], $data['content'] ?? '', $id]);
		return $stmt->rowCount() >= 0;
	}

	public static function deleteActivity($id)
	{
		$id = (int) $id;
		$sql = "DELETE FROM activities WHERE id = ?";
		$stmt = DB::query($sql, [$id]);
		return $stmt->rowCount() > 0;
	}

	public static function getRecentActivities($limit = 6)
	{
		$limit = (int) $limit;
		$sql = "SELECT id, name, activity_time, created_at FROM activities ORDER BY activity_time DESC LIMIT {$limit}";
		$stmt = DB::query($sql);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}
