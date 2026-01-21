<?php
declare(strict_types=1);

require_once __DIR__ . "/../app/core/bootstrap.php";
require_once __DIR__ . "/../app/controllers/auth_controller.php";
require_once __DIR__ . "/../app/models/user_model.php";


$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$base = "/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public";
if (str_starts_with($path, $base)) {
  $path = substr($path, strlen($base));
}
$path = $path ?: "/";


if ($path === "/api/auth/login") {
  require_once __DIR__ . "/../app/controllers/auth_login.php";
  exit;
}

if ($path === "/api/auth/register") {
  require_once __DIR__ . "/../app/controllers/auth_register.php";
  exit;
}

if ($path === "/logout") {
  require_once __DIR__ . "/../app/controllers/logout.php";
  exit;
}


if ($path === "/api/ngo/project/submit") {
  require_once __DIR__ . "/../app/controllers/NgoProjectApiController.php";
  (new NgoProjectApiController())->submit();
  exit;
}

if ($path === "/api/admin/projects") {
  require_once __DIR__ . "/../app/controllers/AdminProjectApiController.php";
  (new AdminProjectApiController())->listPending();
  exit;
}

if ($path === "/api/admin/project/action") {
  require_once __DIR__ . "/../app/controllers/AdminProjectApiController.php";
  (new AdminProjectApiController())->action();
  exit;
}


if ($path === "/" || $path === "/home") { view("home"); exit; }

if ($path === "/login") { view("login"); exit; }
if ($path === "/register") { view("register"); exit; }

if ($path === "/ngo") { view("ngo"); exit; }
if ($path === "/ngo/pending") { view("ngo_pending"); exit; }

if ($path === "/admin") {
    AuthController::adminDashboard();
    exit;
}

if ($path === "/update-status") {
    require_once __DIR__ . "/../app/controllers/AdminController.php";
    AdminController::updateNgoStatus();
    exit;
}

if ($path === "/admin/projects") { view("admin_projects"); exit; }

if ($path === "/donor") { view("donor_dashboard"); exit; }

// fallback
http_response_code(404);
echo "404 Not Found";

