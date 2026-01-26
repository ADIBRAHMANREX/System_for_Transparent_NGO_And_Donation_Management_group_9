<?php
declare(strict_types=1);

require_once __DIR__ . "/../models/project_model.php";
require_once __DIR__ . "/../controllers/auth_controller.php";

final class NgoProjectApiController {

  public function submit(): void {
    AuthController::startSession();
    header("Content-Type: application/json; charset=utf-8");

    $me = $_SESSION["user"] ?? null;
    if (!$me || ($me["role"] ?? "") !== "ngo") {
      http_response_code(401);
      echo json_encode(["success"=>false,"error"=>"Unauthorized"]);
      exit;
    }

    $body = json_decode((string)file_get_contents("php://input"), true) ?: [];
    AuthController::verifyCsrf((string)($body["csrf"] ?? ""));

    $title = trim((string)($body["title"] ?? ""));
    $desc  = trim((string)($body["description"] ?? ""));
    $goal  = (float)($body["goal"] ?? 0);

    if ($title === "" || $desc === "" || $goal <= 0) {
      http_response_code(422);
      echo json_encode(["success"=>false,"error"=>"Title, description and valid goal are required."]);
      exit;
    }

    $id = ProjectModel::createPending((int)$me["id"], $title, $desc, $goal);

    echo json_encode(["success"=>true,"id"=>$id]);
    exit;
  }
}


