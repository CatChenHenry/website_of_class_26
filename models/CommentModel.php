<?php
require_once ROOT_DIR . '/utils/DB.php';

class CommentModel
{
	public static function getCommentsByActivityId($activityId)
	{
		$activityId = (int) $activityId;
		$sql = "SELECT c.id, c.activity_id, c.stu_no, c.username, c.avatar, c.content, c.created_at FROM comments c WHERE c.activity_id = ? ORDER BY c.created_at ASC";
		$stmt = DB::query($sql, [$activityId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function createComment($data)
	{
		$sql = "INSERT INTO comments (activity_id, stu_no, username, avatar, content) VALUES (?, ?, ?, ?, ?)";
		$stmt = DB::query($sql, [$data['activity_id'], $data['stu_no'], $data['username'], $data['avatar'] ?? null, $data['content']]);
		return $stmt->rowCount() > 0;
	}

	public static function deleteComment($id)
	{
		$id = (int) $id;
		$sql = "DELETE FROM comments WHERE id = ?";
		$stmt = DB::query($sql, [$id]);
		return $stmt->rowCount() > 0;
	}

	public static function getCommentById($id)
	{
		$id = (int) $id;
		$sql = "SELECT id, activity_id, stu_no, username, avatar, content, created_at FROM comments WHERE id = ?";
		$stmt = DB::query($sql, [$id]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public static function deleteCommentsByActivityId($activityId)
	{
		$activityId = (int) $activityId;
		$sql = "DELETE FROM comments WHERE activity_id = ?";
		$stmt = DB::query($sql, [$activityId]);
		return $stmt->rowCount() >= 0;
	}
}
