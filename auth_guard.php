<?php
declare(strict_types=1);

function require_login(string $role = ""): array {
  if (session_status() === PHP_SESSION_NONE) session_start();
  $u = $_SESSION["user"] ?? null;
  if (!$u) {
    header("Location: login.php");
    exit;
  }
  if ($role !== "" && (($u["role"] ?? "") !== $role)) {
    header("Location: login.php");
    exit;
  }
  return $u;
}
