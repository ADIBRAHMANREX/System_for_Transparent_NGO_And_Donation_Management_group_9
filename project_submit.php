<?php
declare(strict_types=1);

require_once __DIR__ . "/auth_controller.php";
require_once __DIR__ . "/project_model.php";

AuthController::startSession();
header("Content-Type: application/json; charset=utf-8");

$me = $_SESSION["user"] ?? null;
if (!$me || ($me["role"] ?? "") !== "ngo") {
  http_response_code(401);
  echo json_encode(["success"=>false,"error"=>"Unauthorized"]);
  exit;
}

$body = json_decode((string)file_get_contents("php://input"), true) ?: [];

$csrf = (string)($body["csrf"] ?? "");
$title = trim((string)($body["title"] ?? ""));
$desc  = trim((string)($body["description"] ?? "")); // ✅ expects "description"
$goal  = (float)($body["goal"] ?? 0);

AuthController::verifyCsrf($csrf);

if ($title === "" || $desc === "" || $goal <= 0) {
  http_response_code(422);
  echo json_encode(["success"=>false,"error"=>"Title, description and valid goal are required."]);
  exit;
}

try {
  $id = ProjectModel::createPending((int)$me["id"], $title, $desc, $goal);
  echo json_encode(["success"=>true, "id"=>$id]);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["success"=>false,"error"=>"Server error: ".$e->getMessage()]);
  exit;
}



