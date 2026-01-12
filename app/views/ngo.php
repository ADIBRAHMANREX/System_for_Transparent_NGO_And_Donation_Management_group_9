<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . "/../controllers/auth_controller.php";
require_once __DIR__ . "/../controllers/auth_guard.php";

$csrf = AuthController::csrfToken();





if (!isset($_SESSION['user'])) {
  header("Location: index.html");
  exit;
}

$user = $_SESSION['user'];

if (($user['role'] ?? '') !== 'ngo') {
  header("Location: index.html");
  exit;
}

if (($user['status'] ?? '') !== 'approved') {
  header("Location: ngo_pending.php");
  exit;
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Believe - NGO Dashboard</title>
 <link rel="stylesheet" href="/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public/assets/css/style.css">


  <style>
  
  .dash { max-width:1100px; margin:20px auto; padding:0 18px; }
  .card { background:#fff; padding:14px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06); margin-bottom:12px; }
  .ngo-projects { margin-left:20px; font-size:14px; }
  .ngo-projects li { margin-bottom:4px; }



      
    .dash { max-width:1100px; margin:20px auto; padding:0 18px; }
    .card { background:#fff; padding:14px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06); margin-bottom:12px; }

    .ngo-table { width:100%; border-collapse:collapse; font-size:14px; margin-top:8px; }
    .ngo-table th, .ngo-table td {
      border:1px solid #eee;
      padding:6px 8px;
      text-align:left;
    }
    .ngo-table th { background:#f7f9fc; }

    .ngo-projects { list-style:disc; margin-left:20px; margin-top:6px; }
    .ngo-projects li { margin-bottom:4px; }

   /* Header layout */
.head {
    display: flex;
    justify-content: space-between; /* brand left, logout right */
    align-items: center;
    padding: 12px 25px;
    background: #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Brand name */
.brand {
    font-size: 26px;
    font-weight: bold;
    color: #1656a2;
    text-decoration: none;
}

/* Logout button */
.logout-btn {
    background: #1656a2;
    color: white;
    border: none;
    padding: 8px 16px;
    font-size: 14px;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.2s;
}

.logout-btn:hover {
    background: #0e3e76;
}

/* Status badge (same idea as donor side) */
.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

/* Colors for each status */
.status-processing {
    background: #fff7e6;
    color: #b26b00;
}

.status-received {
    background: #e6f4ff;
    color: #0b63b6;
}

.status-implementing {
    background: #f3e6ff;
    color: #6b2fb6;
}

.status-completed {
    background: #e6f7e9;
    color: #1f7a34;
}

.status-reported {
    background: #e6fffb;
    color: #00796b;
}

.status-flagged {
    background: #ffe6e6;
    color: #b21f1f;
}

/* Small action button inside the table */
.status-action-btn {
    padding: 4px 10px;
    font-size: 12px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    background: #1656a2;
    color: #fff;
}

.status-action-btn:hover {
    background: #0e3e76;
}


  

  </style>

  <?php

?>
<script>
  window.PHP_CSRF = "<?= htmlspecialchars($csrf) ?>";
</script>


</head>
<body>
  <header class="head">
  <a href="index.html" class="brand">Believe</a>

  <a href="logout.php" class="logout-btn" style="text-decoration:none;display:inline-block;">
  Logout
</a>

</header>

    <main class="dash">
    <h2>NGO Dashboard</h2>

    <!-- NGO summary + compliance -->
    <div class="card">
      <h3 id="ngo-name"><?= htmlspecialchars($user['name']) ?></h3>

      <p>Compliance status: <strong id="ngo-status">Unverified</strong></p>
      <p style="font-size:14px;color:#555;">
        This is a frontend demo. Status is based on whether this NGO has any verified projects in <code>projects.xml</code>.
      </p>
    </div>

    <!-- Incoming donations for this NGO -->
    <div class="card">
  <h3>Incoming Donations (demo)</h3>
  <table class="ngo-table">
    <thead>
      <tr>
        <th>Project</th>
        <th>Date</th>
        <th>Amount (BDT)</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="ngo-donations-body">
      <tr><td colspan="5">Loading...</td></tr>
    </tbody>
  </table>
</div>


    <!-- Active projects for this NGO -->
    <div class="card">
  <h3>Active Projects</h3>
  <ul id="ngo-projects-list" class="ngo-projects">
    <li>Loading...</li>
  </ul>
  <p style="font-size:13px;color:#777;margin-top:8px;">
    Projects are loaded from <code>projects.xml</code> based on this NGO’s name.
  </p>
</div>


<div class="card">
  <h3>Incoming Projects (Pending Admin Approval)</h3>
  <p id="ngoNoIncoming" style="display:none;color:#777;">No pending projects.</p>
  <div id="ngoIncomingList"></div>
</div>

<div class="card">
  <h3>Approved Projects</h3>
  <p id="ngoNoApproved" style="display:none;color:#777;">No approved projects yet.</p>
  <div id="ngoApprovedList"></div>
</div>

<div class="card">
  <h3>Ongoing Projects</h3>
  <p id="ngoNoOngoing" style="display:none;color:#777;">No ongoing projects.</p>
  <div id="ngoOngoingList"></div>
</div>


<!-- NGO Project Submission Section -->
<div class="card" id="projectSubmissionCard" style="margin-top:20px;">
  <h3>Submit New Project</h3>

  <form id="projectSubmissionForm">
    <label>Project Title:</label><br>
    <input type="text" id="projTitle" required style="width:100%; padding:7px; margin-bottom:10px;"><br>

    <label>Short Description:</label><br>
    <textarea id="projDesc" required style="width:100%; padding:7px; margin-bottom:10px;"></textarea><br>

    <label>Goal Amount (BDT):</label><br>
    <input type="number" id="projGoal" required min="1" style="width:100%; padding:7px; margin-bottom:10px;"><br>

    <button type="submit" class="btn" style="padding:8px 18px;">Submit Project</button>
  </form>

  <p id="projSubmitMsg" style="color:green; font-size:14px; margin-top:10px; display:none;">
    Project submitted! Waiting for Admin approval.
  </p>
</div>


  </main>



<script>
  window.PHP_SESSION_USER = <?= json_encode($_SESSION['user'], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="../public/assets/js/ngo_js.js"></script>


</body>
</html>
