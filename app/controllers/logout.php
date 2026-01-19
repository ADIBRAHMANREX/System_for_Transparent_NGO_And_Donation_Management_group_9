<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$_SESSION = [];
session_destroy();

// ✅ redirect to MVC login route (NOT login.php)
header("Location: /webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public/login");
exit;

