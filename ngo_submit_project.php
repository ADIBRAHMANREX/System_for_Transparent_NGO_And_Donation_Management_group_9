<?php
declare(strict_types=1);

require_once __DIR__ . "/auth_controller.php";
require_once __DIR__ . "/config_db.php";

AuthController::startSession();

// must be logged in
if (!isset($_SESSION["user"])) {
  http_response_code(401);
  header("Content-Type: application/json");
  echo json_encode(["success"=>false,"error"=>"Not logged in"]);
  exit;
}

// must be NGO + approved
$u = $_SESSION["user"];
if (($u["role"] ?? "") !== "ngo") {
  http_response_code(403);
  header("Content-Type: application/json");
  echo json_encode(["success"=>false,"error"=>"Only NGO can submit projects"]);
  exit;
}
if (($u["status"] ?? "") !== "approved") {
  http_response_code(403);
  header("Content-Type: application/json");
  echo json_encode(["success"=>false,"error"=>"NGO not approved"]);
  exit;
}

// read JSON
$body = json_decode((string)file_get_contents("php://input"), true) ?: [];

// CSRF check (same logic as AuthController)
$token = (string)($body["csrf"] ?? "");
if (empty($_SESSION["csrf"]) || !hash_equals($_SESSION["csrf"], $token)) {
  http_response_code(400);
  header("Content-Type: application/json");
  echo json_encode(["success"=>false,"error"=>"Invalid CSRF token."]);
  exit;
}

$title = trim((string)($body["title"] ?? ""));
$desc  = trim((string)($body["desc"] ?? ""));
$goal  = (int)($body["goal"] ?? 0);

if ($title === "" || $desc === "" || $goal <= 0) {
  http_response_code(422);
  header("Content-Type: application/json");
  echo json_encode(["success"=>false,"error"=>"Title/Description/Goal required"]);
  exit;
}

$pdo = db();

// Insert as pending (admin will approve)
$stmt = $pdo->prepare("
  INSERT INTO projects (ngo_user_id, title, description, goal_amount, raised_amount, status, created_at)
  VALUES (:ngo_user_id, :title, :description, :goal_amount, 0, 'pending', NOW())
");
$stmt->execute([
  ":ngo_user_id" => (int)$u["id"],
  ":title" => $title,
  ":description" => $desc,
  ":goal_amount" => $goal
]);

header("Content-Type: application/json");
echo json_encode(["success"=>true, "project_id" => (int)$pdo->lastInsertId()]);
