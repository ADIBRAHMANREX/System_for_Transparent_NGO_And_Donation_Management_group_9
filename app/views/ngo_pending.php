<?php
session_start();

/*
Expected session structure after login:
$_SESSION['user'] = [
  'id' => ...,
  'email' => ...,
  'role' => 'ngo',
  'status' => 'pending'
];
*/

// Not logged in → login
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$user = $_SESSION['user'];

// If not NGO → redirect
if ($user['role'] !== 'ngo') {
  header("Location: donor_dashboard.php");
  exit;
}

// If NGO already approved → dashboard
if ($user['status'] === 'approved') {
  header("Location: ngo.html");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Believe - NGO Approval Pending</title>
  <link rel="stylesheet" href="style.css"/>
  <style>
    .pending-wrap {
      max-width: 480px;
      margin: 60px auto;
      padding: 24px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      text-align: center;
    }
    .pending-status {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 999px;
      background: #fff7e6;
      color: #b26b00;
      font-size: 13px;
      font-weight: 600;
      margin: 10px 0 14px;
    }
    .btn {
      padding: 10px 16px;
      border-radius: 6px;
      border: none;
      background: #1656a2;
      color: #fff;
      cursor: pointer;
      margin-top: 14px;
    }
  </style>
</head>

<body>
<header class="top-bar">
  <a href="/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public" class="brand">Believe</a>
</header>

<main class="pending-wrap">
  <h2>NGO Approval Pending</h2>

  <div class="pending-status">
    Waiting for Admin Approval
  </div>

  <p>
    Thank you for registering your NGO with <strong>Believe</strong>.
  </p>

  <p style="font-size:14px;color:#555;">
    Your account is currently under review by the platform administrator.
  </p>

  <ul style="text-align:left; max-width:340px; margin:16px auto; font-size:14px;">
    <li>Access NGO dashboard after approval</li>
    <li>Submit and manage projects</li>
    <li>Receive and track donations</li>
  </ul>

  <form method="post" action="logout.php">
    <a class="btn secondary" href="/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public/logout"
>Logout</a>
  </form>
</main>

</body>
</html>
