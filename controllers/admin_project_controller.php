<?php
declare(strict_types=1);

require_once __DIR__ . "/auth_controller.php";
require_once __DIR__ . "/project_model.php";

AuthController::startSession();

// Only admin allowedjj
if (!isset($_SESSION["user"]) || $_SESSION["user"]["role"] !== "admin") {
  http_response_code(403);
  echo json_encode(["success"=>false,"error"=>"Unauthorized"]);
  exit;
}

$method = $_SERVER["REQUEST_METHOD"];

// GET → fetch pending projects
if ($method === "GET") {
  $projects = ProjectModel::pending();
  echo json_encode(["success"=>true,"projects"=>$projects]);
  exit;
}

// POST → approve project
if ($method === "POST") {
  $body = json_decode(file_get_contents("php://input"), true) ?? [];

  if (empty($body["csrf"]) || !hash_equals($_SESSION["csrf"], $body["csrf"])) {
    http_response_code(400);
    echo json_encode(["success"=>false,"error"=>"Invalid CSRF token"]);
    exit;
  }

  $id = (int)($body["project_id"] ?? 0);
  if ($id <= 0) {
    http_response_code(422);
    echo json_encode(["success"=>false,"error"=>"Invalid project"]);
    exit;
  }

  ProjectModel::approve($id);
  echo json_encode(["success"=>true]);
  exit;
}
