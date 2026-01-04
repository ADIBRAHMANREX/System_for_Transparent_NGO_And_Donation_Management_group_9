<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION["user"])) {
  header("Location: login.php");
  exit;
}
if (($_SESSION["user"]["role"] ?? "") !== "admin") {
  header("Location: login.php");
  exit;
}
if (empty($_SESSION["csrf"])) {
  $_SESSION["csrf"] = bin2hex(random_bytes(16));
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin - Project Approvals</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f6f7f9;margin:0}
    .wrap{max-width:1000px;margin:20px auto;padding:0 16px}
    .top{display:flex;justify-content:space-between;align-items:center}
    .card{background:#fff;padding:14px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-top:14px}
    table{width:100%;border-collapse:collapse;margin-top:10px}
    th,td{border:1px solid #eee;padding:8px;text-align:left;font-size:14px}
    th{background:#f3f5f8}
    .btn{border:none;border-radius:6px;padding:6px 10px;cursor:pointer}
    .ok{background:#0b7a39;color:#fff}
    .bad{background:#b11f1f;color:#fff}
    a{color:#1656a2;text-decoration:none}
  </style>
  <script>
    window.PHP_CSRF = "<?= htmlspecialchars($_SESSION["csrf"]) ?>";
  </script>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <h2>Project Approval (Admin)</h2>
      <div>
        <a href="admin_dashboard.php">NGO Approvals</a> |
        <a href="logout.php">Logout</a>
      </div>
    </div>

    <div class="card">
      <h3>Pending Projects</h3>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>NGO</th>
            <th>Title</th>
            <th>Goal</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="pendingBody">
          <tr><td colspan="6">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

<script>
async function loadPending(){
  const body = document.getElementById("pendingBody");
  const res = await fetch("admin_project_controller.php?action=list_pending");
  const data = await res.json();

  if(!data.success){
    body.innerHTML = `<tr><td colspan="6">${data.error || "Failed"}</td></tr>`;
    return;
  }

  if(!data.projects.length){
    body.innerHTML = `<tr><td colspan="6">No pending projects.</td></tr>`;
    return;
  }

  body.innerHTML = "";
  data.projects.forEach(p => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${p.id}</td>
      <td>${p.ngo_name || ""} (${p.ngo_email || ""})</td>
      <td>${p.title}</td>
      <td>৳${Number(p.goal).toLocaleString()}</td>
      <td>${p.status}</td>
      <td>
        <button class="btn ok" onclick="approve(${p.id})">Approve</button>
        <button class="btn bad" onclick="reject(${p.id})">Reject</button>
      </td>
    `;
    body.appendChild(tr);
  });
}

async function approve(id){
  const res = await fetch("admin_project_controller.php?action=approve", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({ csrf: window.PHP_CSRF, id })
  });
  const data = await res.json();
  if(!data.success) return alert(data.error || "Approve failed");
  loadPending();
}

async function reject(id){
  const res = await fetch("admin_project_controller.php?action=reject", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({ csrf: window.PHP_CSRF, id })
  });
  const data = await res.json();
  if(!data.success) return alert(data.error || "Reject failed");
  loadPending();
}

loadPending();
</script>
</body>
</html>

