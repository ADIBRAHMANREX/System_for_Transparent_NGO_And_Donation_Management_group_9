<?php
declare(strict_types=1);

require_once __DIR__ . "/auth_controller.php";
require_once __DIR__ . "/project_model.php";

AuthController::startSession();

function json_out(array $p, int $code=200): void {
  http_response_code($code);
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode($p);
  exit;
}

$u = $_SESSION["user"] ?? null;
if (!$u) json_out(["success"=>false,"error"=>"Not logged in."], 401);
if (($u["role"] ?? "") !== "ngo") json_out(["success"=>false,"error"=>"Forbidden."], 403);
if (($u["status"] ?? "") !== "approved") json_out(["success"=>false,"error"=>"NGO not approved yet."], 403);

$body = json_decode((string)file_get_contents("php://input"), true) ?: [];
$csrf = (string)($body["csrf"] ?? "");

if (empty($_SESSION["csrf"]) || !hash_equals($_SESSION["csrf"], $csrf)) {
  json_out(["success"=>false,"error"=>"Invalid CSRF token."], 400);
}

$title = trim((string)($body["title"] ?? ""));
$desc  = trim((string)($body["desc"] ?? ""));
$goal  = (float)($body["goal"] ?? 0);

if ($title === "" || $desc === "") json_out(["success"=>false,"error"=>"Title/description required."], 422);
if ($goal <= 0) json_out(["success"=>false,"error"=>"Goal must be > 0."], 422);

$projectId = ProjectModel::createPending((int)$u["id"], $title, $desc, $goal);

json_out(["success"=>true, "project_id"=>$projectId]);
