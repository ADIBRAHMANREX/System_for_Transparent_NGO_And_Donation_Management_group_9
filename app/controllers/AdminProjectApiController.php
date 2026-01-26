<?php
declare(strict_types=1);

require_once __DIR__ . "/../models/project_model.php";
require_once __DIR__ . "/../controllers/auth_controller.php";

final class AdminProjectApiController {

  public function listPending(): void {
    AuthController::requireLogin("admin");
    header("Content-Type: application/json; charset=utf-8");

    $rows = ProjectModel::listPending();
    echo json_encode(["success"=>true, "projects"=>$rows]);
    exit;
  }

  public function action(): void {
    AuthController::requireLogin("admin");
    header("Content-Type: application/json; charset=utf-8");

    $body = json_decode((string)file_get_contents("php://input"), true) ?: [];
    AuthController::verifyCsrf((string)($body["csrf"] ?? ""));

    $id = (int)($body["id"] ?? 0);
    $status = (string)($body["status"] ?? "");

    if ($id <= 0 || !in_array($status, ["approved","rejected"], true)) {
      http_response_code(422);
      echo json_encode(["success"=>false,"error"=>"Invalid request"]);
      exit;
    }

    ProjectModel::updateStatus($id, $status);
    echo json_encode(["success"=>true]);
    exit;
  }
}


