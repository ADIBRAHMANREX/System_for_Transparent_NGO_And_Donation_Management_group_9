<?php
declare(strict_types=1);

require_once __DIR__ . "/../models/user_model.php";


final class AuthController {



public static function adminDashboard(): void {
    self::startSession();

    $me = $_SESSION["user"] ?? null;
    if (!$me || ($me["role"] ?? "") !== "admin") {
        header("Location: login");
        exit;
    }

    $ngos = UserModel::listNGOs();

    view("admin_dashboard", [
        "ngos" => $ngos
    ]);
}



  public static function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  public static function csrfToken(): string {
    self::startSession();
    if (empty($_SESSION["csrf"])) {
      $_SESSION["csrf"] = bin2hex(random_bytes(16));
    }
    return $_SESSION["csrf"];
  }

  private static function requireCsrf(string $token): void {
    self::startSession();
    if (empty($_SESSION["csrf"]) || !hash_equals($_SESSION["csrf"], $token)) {
      self::json(["success" => false, "error" => "Invalid CSRF token."], 400);
    }
  }

  // ✅ Public wrapper (ONLY ONCE)
  public static function verifyCsrf(string $token): void {
    self::requireCsrf($token);
  }

  public static function requireLogin(string $role = ""): void {
    self::startSession();
    if (empty($_SESSION["user"])) {
      self::json(["success"=>false, "error"=>"Not logged in"], 401);
    }
    if ($role !== "" && (($_SESSION["user"]["role"] ?? "") !== $role)) {
      self::json(["success"=>false, "error"=>"Forbidden"], 403);
    }
  }

  private static function json(array $payload, int $code = 200): void {
    http_response_code($code);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($payload);
    exit;
  }

  public static function checkEmail(): void {
    $email = trim((string)($_GET["email"] ?? ""));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      self::json(["exists" => false]);
    }
    $u = UserModel::findByEmail($email);
    self::json(["exists" => $u ? true : false]);
  }

  public static function register(): void {
    self::startSession();
    $body = json_decode((string)file_get_contents("php://input"), true) ?: [];

    self::requireCsrf((string)($body["csrf"] ?? ""));

    $first = trim((string)($body["first_name"] ?? ""));
    $last  = trim((string)($body["last_name"] ?? ""));
    $email = trim((string)($body["email"] ?? ""));
    $pw    = (string)($body["password"] ?? "");
    $role  = (string)($body["role"] ?? "donor");

    if ($first === "" || $last === "") self::json(["success"=>false,"error"=>"Name is required."], 422);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) self::json(["success"=>false,"error"=>"Invalid email."], 422);
    if (strlen($pw) < 8) self::json(["success"=>false,"error"=>"Password must be at least 8 characters."], 422);
    if (!in_array($role, ["donor","ngo"], true)) self::json(["success"=>false,"error"=>"Invalid role."], 422);

    if (UserModel::findByEmail($email)) {
      self::json(["success"=>false,"error"=>"Email already exists."], 409);
    }

    $status = ($role === "ngo") ? "pending" : "approved";

    $id = UserModel::create([
      "first_name" => $first,
      "last_name" => $last,
      "email" => $email,
      "password_hash" => password_hash($pw, PASSWORD_BCRYPT),
      "role" => $role,
      "status" => $status
    ]);

    self::json([
      "success" => true,
      "user" => ["id"=>$id, "email"=>$email, "role"=>$role, "status"=>$status]
    ]);
  }

  public static function login(): void {
    self::startSession();
    $body = json_decode((string)file_get_contents("php://input"), true) ?: [];

    self::requireCsrf((string)($body["csrf"] ?? ""));

    $email = trim((string)($body["email"] ?? ""));
    $pw    = (string)($body["password"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) self::json(["success"=>false,"error"=>"Invalid email."], 422);

    $user = UserModel::findByEmail($email);
    if (!$user || !password_verify($pw, (string)$user["password_hash"])) {
      self::json(["success"=>false,"error"=>"Email or password incorrect."], 401);
    }

    session_regenerate_id(true);

    $_SESSION["user"] = [
      "id" => (int)$user["id"],
      "email" => (string)$user["email"],
      "name" => (string)$user["first_name"] . " " . (string)$user["last_name"],
      "role" => (string)$user["role"],
      "status" => (string)$user["status"]
    ];

    self::json(["success"=>true, "user"=>$_SESSION["user"]]);
  }

  public static function logout(): void {
    self::startSession();
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit;
  }
}


