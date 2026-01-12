<?php
declare(strict_types=1);


require_once 'session_timeout.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

if (($_SESSION['user']['role'] ?? '') !== 'donor') {
    header("Location: login.php");
    exit;
}


$user = $_SESSION["user"];
if (($user["role"] ?? "") !== "donor") {
  header("Location: index.html");
  exit;
}

$payload = [
  "name" => $user["name"] ?? "Donor",
  "email" => $user["email"] ?? "",
  "role" => $user["role"] ?? "donor",
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Donor Dashboard</title>
  <link rel="stylesheet" href="/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public/assets/css/style.css">

</head>
<body>
  <div class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <div class="brand-badge" aria-hidden="true"></div>
        <div>
          <div>Believe</div>
          <div class="muted" style="font-weight:600; font-size:12px;">Donor Dashboard</div>
        </div>
      </div>
      <div class="top-actions">
        <button class="btn" id="themeToggle" type="button">Theme</button>
        <button class="btn btn-danger" id="logoutBtn" type="button">Logout</button>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="card">
      <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:baseline; justify-content:space-between;">
        <div>
          <h2 style="margin:0;">Welcome, <span id="donorName"></span></h2>
          <div class="muted" style="margin-top:6px;">Email: <span id="donorEmail"></span></div>
        </div>
        <div class="badge good" id="donorBadge">Active Donor</div>
      </div>

      <div class="hr"></div>

      <div class="stats">
        <div class="stat">
          <div class="k">Total Donated</div>
          <div class="v" id="statTotal">৳0</div>
        </div>
        <div class="stat">
          <div class="k">Donations</div>
          <div class="v" id="statCount">0</div>
        </div>
        <div class="stat">
          <div class="k">Projects Supported</div>
          <div class="v" id="statProjects">0</div>
        </div>
        <div class="stat">
          <div class="k">Last Donation</div>
          <div class="v" id="statLast">—</div>
        </div>
      </div>
    </div>

    <div class="grid">
      <div class="card" style="grid-column: span 12;">
        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between;">
          <h3 style="margin:0;">Donation History</h3>
          <div class="controls">
            <input id="historySearch" class="input" type="search" placeholder="Search by project..." autocomplete="off">
            <select id="historyProject" class="small">
              <option value="">All projects</option>
            </select>
            <select id="historyStatus" class="small">
              <option value="">All status</option>
              <option value="success">Success</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
            </select>
            <button class="btn btn-primary" id="exportCsv" type="button">Export CSV</button>
          </div>
        </div>

        <div class="hr"></div>

        <table class="table">
          <thead>
            <tr>
              <th>Project</th>
              <th>Date</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="historyBody">
            <tr><td colspan="4" class="muted">No donations yet.</td></tr>
          </tbody>
        </table>
      </div>

      <div class="card" style="grid-column: span 12;">
        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between;">
          <h3 style="margin:0;">Projects</h3>
          <div class="controls">
            <input id="projectSearch" class="input" type="search" placeholder="Search projects..." list="projectSuggestions" autocomplete="off">
            <datalist id="projectSuggestions"></datalist>

            <select id="projectSort" class="small">
              <option value="recommended">Recommended</option>
              <option value="mostRaised">Most raised</option>
              <option value="highestProgress">Highest progress</option>
              <option value="lowestGoal">Lowest goal</option>
              <option value="az">A → Z</option>
            </select>

            <label class="badge" style="cursor:pointer; user-select:none;">
              <input type="checkbox" id="favOnly" style="margin:0 8px 0 0; accent-color: var(--primary);">
              Favorites only
            </label>
          </div>
        </div>

        <div class="hr"></div>

        <div class="project-grid" id="projectsGrid"></div>
      </div>
    </div>
  </div>

  <script>
    window.DONOR_USER = <?php echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  </script>
  <script src="donor_script.js"></script>
</body>
</html>
