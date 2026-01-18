<?php
declare(strict_types=1);

require_once __DIR__ . "/../models/config_db.php";

final class UserModel {

  public static function findByEmail(string $email): ?array {
    $stmt = db()->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(["email" => $email]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  public static function create(array $data): int {
    $stmt = db()->prepare("
      INSERT INTO users (first_name, last_name, email, password_hash, role, status)
      VALUES (:first_name, :last_name, :email, :password_hash, :role, :status)
    ");
    $stmt->execute($data);
    return (int)db()->lastInsertId();
  }

  public static function listNGOs(): array {
    $stmt = db()->query("
      SELECT id, first_name, last_name, email, role, status, created_at
      FROM users
      WHERE role = 'ngo'
      ORDER BY created_at DESC
    ");
    return $stmt->fetchAll();
  }

  public static function setStatus(int $id, string $status): void {
    $stmt = db()->prepare("UPDATE users SET status = :status WHERE id = :id");
    $stmt->execute(["status" => $status, "id" => $id]);
  }
}
