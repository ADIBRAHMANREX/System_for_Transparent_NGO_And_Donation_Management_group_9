<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . "/project_model.php";

header("Content-Type: application/json; charset=utf-8");

function jsonOut(array $p, int $code = 200): void {
  http_response_code($code);
  echo json_encode($p);
  exit;
}

if (!isset($_SESSION["user"])) {
  jsonOut(["success"=>false, "error"=>"Not logged in"], 401);
}

$user = $_SESSION["user"];
if (($user["role"] ?? "") !== "ngo") {
  jsonOut(["success"=>false, "error"=>"Only NGO can submit projects"], 403);
}

if (($user["status"] ?? "") !== "approved") {
  jsonOut(["success"=>false, "error"=>"NGO not approved"], 403);
}

$body = json_decode((string)file_get_contents("php://input"), true) ?: [];

$token = (string)($body["csrf"] ?? "");
if (empty($_SESSION["csrf"]) || !hash_equals($_SESSION["csrf"], $token)) {
  jsonOut(["success"=>false, "error"=>"Invalid CSRF token."], 400);
}

$title = trim((string)($body["title"] ?? ""));
$desc  = trim((string)($body["description"] ?? ""));
$goal  = (int)($body["goal"] ?? 0);

if ($title === "" || $desc === "" || $goal <= 0) {
  jsonOut(["success"=>false, "error"=>"Title, description, and goal are required."], 422);
}

try {
  $projectId = ProjectModel::createPending([
    "ngo_user_id" => (int)$user["id"],
    "title" => $title,
    "description" => $desc,
    "goal" => $goal
  ]);

  jsonOut(["success"=>true, "id"=>$projectId]);
} catch (Throwable $e) {
  jsonOut(["success"=>false, "error"=>"DB error: " . $e->getMessage()], 500);
}

