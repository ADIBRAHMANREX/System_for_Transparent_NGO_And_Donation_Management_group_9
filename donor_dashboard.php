<?php
declare(strict_types=1);
session_start();

// 1) Not logged in → go to index
if (!isset($_SESSION["user"])) {
  header("Location: index.html");
  exit;
}

$user = $_SESSION["user"];

// 2) Not a donor → go to index
if (($user["role"] ?? "") !== "donor") {
  header("Location: index.html");
  exit;
}

// optional: regenerate token/pages etc (not required here)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Donor Dashboard - Believe</title>
  <link rel="stylesheet" href="donor_style.css">
</head>

<body>

<div class="header">
  <h1>Believe - Donor Dashboard</h1>
  <button id="logoutBtn">Logout</button>
</div>

<div class="profile-box">
  <h2>Welcome, <span id="donorName">User</span></h2>
  <p>Email: <span id="donorEmail">example@email.com</span></p>
</div>

<hr>

<h2>Your Donation History</h2>
<table class="history-table">
  <tr>
    <th>Project</th>
    <th>Date</th>
    <th>Amount</th>
    <th>Status</th>
  </tr>
  <tbody id="historyBody">
    <!-- rows injected by JS -->
  </tbody>
</table>

<hr>

<h2>Ongoing Projects</h2>

<p id="noProjectsMsg" style="display:none;">No approved projects yet.</p>

<div id="projectList">
  <!-- Approved projects will be injected by JS -->
</div>

<!-- ✅ Make PHP session user available to JS -->
<script>
  window.PHP_SESSION_USER = <?= json_encode([
    "id" => $user["id"] ?? null,
    "name" => $user["name"] ?? "",
    "email" => $user["email"] ?? "",
    "role" => $user["role"] ?? "",
    "status" => $user["status"] ?? ""
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
</script>

<script src="donor_script.js"></script>

</body>
</html>
