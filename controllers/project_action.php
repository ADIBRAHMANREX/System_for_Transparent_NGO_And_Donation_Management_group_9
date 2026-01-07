<?php
declare(strict_types=1);

require_once __DIR__ . "/auth_controller.php";
require_once __DIR__ . "/project_model.php";

AuthController::startSession();

$user = $_SESSION["user"] ?? null;
if (!$user) { header("Location: login.php"); exit; }
if (($user["role"] ?? "") !== "admin") { header("Location: index.html"); exit; }

if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: admin_dashboard.php"); exit; }

$csrf = (string)($_POST["csrf"] ?? "");
if (empty($_SESSION["csrf"]) || !hash_equals($_SESSION["csrf"], $csrf)) {
  die("Invalid CSRF token.");
}

$projectId = (int)($_POST["project_id"] ?? 0);
$action = (string)($_POST["action"] ?? "");

if ($projectId <= 0) { header("Location: admin_dashboard.php"); exit; }

if ($action === "approve") ProjectModel::updateStatus($projectId, "approved");
if ($action === "reject")  ProjectModel::updateStatus($projectId, "rejected");

header("Location: admin_dashboard.php");
exit;
