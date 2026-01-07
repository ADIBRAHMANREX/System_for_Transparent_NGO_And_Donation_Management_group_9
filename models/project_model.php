<?php
declare(strict_types=1);

require_once __DIR__ . "/config_db.php";

final class ProjectModel {

  public static function createPending(int $ngoUserId, string $title, string $desc, float $goal): int {
    $pdo = db();
    $stmt = $pdo->prepare("
      INSERT INTO projects (ngo_user_id, title, description, goal, status)
      VALUES (?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$ngoUserId, $title, $desc, $goal]);
    return (int)$pdo->lastInsertId();
  }

  public static function listPending(): array {
    $pdo = db();
    return $pdo->query("
      SELECT p.*, CONCAT(u.first_name,' ',u.last_name) AS ngo_name, u.email AS ngo_email
      FROM projects p
      JOIN users u ON u.id = p.ngo_user_id
      WHERE p.status='pending'
      ORDER BY p.created_at DESC
    ")->fetchAll();
  }

  public static function updateStatus(int $projectId, string $status): void {
    $pdo = db();
    $stmt = $pdo->prepare("UPDATE projects SET status=? WHERE id=?");
    $stmt->execute([$status, $projectId]);
  }

  public static function listApproved(): array {
    $pdo = db();
    return $pdo->query("
      SELECT p.*, CONCAT(u.first_name,' ',u.last_name) AS ngo_name
      FROM projects p
      JOIN users u ON u.id = p.ngo_user_id
      WHERE p.status='approved'
      ORDER BY p.created_at DESC
    ")->fetchAll();
  }
}
