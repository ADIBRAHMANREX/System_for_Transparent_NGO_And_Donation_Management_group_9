<?php
declare(strict_types=1);

require_once __DIR__ . "/../controllers/auth_guard.php";
require_once __DIR__ . "/../controllers/auth_controller.php";

/* ---------------- LOGIN + ROLE CHECK ---------------- */
$user = require_login("ngo");

/* NGO must be approved */
if (($user["status"] ?? "") !== "approved") {
    header("Location: /ngo/pending");
    exit;
}

/* CSRF */
$csrf = AuthController::csrfToken();

/* Base path (optional helper) */
$APP_BASE = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Believe - NGO Dashboard</title>

<link rel="stylesheet" href="/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public/assets/css/style.css">

<script>
window.PHP_CSRF = "<?= htmlspecialchars($csrf,ENT_QUOTES,'UTF-8') ?>";
window.APP_BASE = "<?= htmlspecialchars($APP_BASE,ENT_QUOTES,'UTF-8') ?>";
window.PHP_SESSION_USER = <?= json_encode($user,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>

<style>
.head{display:flex;justify-content:space-between;align-items:center;padding:12px 25px;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,.1)}
.brand{font-size:26px;font-weight:bold;color:#1656a2;text-decoration:none}
.logout-btn{background:#1656a2;color:white;border:none;padding:8px 16px;border-radius:6px}
.logout-btn:hover{background:#0e3e76}
.dash{max-width:1100px;margin:20px auto;padding:0 18px}
.card{background:#fff;padding:14px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.06);margin-bottom:12px}
.ngo-table{width:100%;border-collapse:collapse;font-size:14px}
.ngo-table th,.ngo-table td{border:1px solid #eee;padding:6px}
.ngo-table th{background:#f7f9fc}
</style>

</head>

<body>

<header class="head">
<a href="/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public" class="brand">Believe</a>

<a class="logout-btn"
href="/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public/logout">
Logout</a>
</header>

<main class="dash">

<h2>NGO Dashboard</h2>

<div class="card">
<h3><?= htmlspecialchars($user["name"] ?? "NGO") ?></h3>
<p>Status: Approved</p>
</div>

<div class="card">
<h3>Active Projects</h3>
<ul id="ngo-projects-list"><li>Loading...</li></ul>
</div>

<div class="card">
<h3>Incoming Donations</h3>
<table class="ngo-table">
<thead>
<tr>
<th>Project</th>
<th>Date</th>
<th>Amount</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody id="ngo-donations-body">
<tr><td colspan="5">Loading...</td></tr>
</tbody>
</table>
</div>

<div class="card" id="projectSubmissionCard">
<h3>Submit New Project</h3>

<form id="projectSubmissionForm">

<label>Project Title</label>
<input id="projTitle" required style="width:100%;padding:7px">

<label>Description</label>
<textarea id="projDesc" required style="width:100%;padding:7px"></textarea>

<label>Goal Amount</label>
<input id="projGoal" type="number" min="1" required style="width:100%;padding:7px">

<button type="submit" class="btn">Submit Project</button>
</form>

<p id="projSubmitMsg" style="display:none;color:green;margin-top:10px">
Project submitted! Waiting for Admin approval.
</p>

</div>

</main>

<script src="/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public/assets/js/ngo_js.js"></script>

</body>
</html>

