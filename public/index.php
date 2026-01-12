<?php
declare(strict_types=1);

require_once __DIR__ . "/../app/core/bootstrap.php";

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// adjust this to your folder name if needed
$base = "/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public";
if (str_starts_with($path, $base)) {
  $path = substr($path, strlen($base));
}
$path = $path ?: "/";

// API routes
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

// Page routes
if ($path === "/" || $path === "/home") {
  view("home");
  exit;
}
if ($path === "/login") { view("login"); exit; }
if ($path === "/register") { view("register"); exit; }
if ($path === "/ngo") { view("ngo"); exit; }
if ($path === "/admin/projects") { view("admin_projects"); exit; }

http_response_code(404);
echo "404 Not Found";
