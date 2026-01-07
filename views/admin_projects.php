<?php
declare(strict_types=1);
require_once __DIR__ . "/auth_guard.php";
require_once __DIR__ . "/auth_controller.php";

$me = require_login("admin");
$csrf = AuthController::csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Admin - Project Approval</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f5f6f7;margin:0}
    .wrap{max-width:980px;margin:20px auto;background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;font-size:14px}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
    .btn{padding:8px 10px;border:none;border-radius:8px;background:#111;color:#fff;cursor:pointer}
    .btn.secondary{background:#eee;color:#111}
    .pill{display:inline-block;padding:3px 8px;border-radius:999px;background:#f1f3f5;font-size:12px}
  </style>
  <script>
    window.PHP_CSRF = "<?= htmlspecialchars($csrf) ?>";
  </script>
</head>
<body>
<div class="wrap">
  <div class="top">
    <h2>Project Requests</h2>
    <div>
      <a class="btn secondary" href="admin_dashboard.php" style="text-decoration:none;display:inline-block;">NGO Approvals</a>
      <a class="btn secondary" href="logout.php" style="text-decoration:none;display:inline-block;">Logout</a>
    </div>
  </div>

  <p style="color:#666;font-size:14px;margin-top:0">
    Pending projects submitted by approved NGOs.
  </p>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>NGO</th>
        <th>Title</th>
        <th>Description</th>
        <th>Goal</th>
        <th>Status</th>
        <th>Created</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="pendingBody">
      <tr><td colspan="8">Loading...</td></tr>
    </tbody>
  </table>
</div>

<script>
async function loadPending(){
  const body = document.getElementById("pendingBody");
  body.innerHTML = '<tr><td colspan="8">Loading...</td></tr>';

  try{
    const res = await fetch("admin_project_controller.php");
    const data = await res.json();

    if(!data.success){
      body.innerHTML = `<tr><td colspan="8">${data.error || "Failed to load."}</td></tr>`;
      return;
    }

    const rows = data.projects || [];
    if(rows.length === 0){
      body.innerHTML = `<tr><td colspan="8">No pending projects.</td></tr>`;
      return;
    }

    body.innerHTML = "";
    rows.forEach(p => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${p.id}</td>
        <td>${escapeHtml(p.ngo_name || "")}<br><span class="pill">${escapeHtml(p.ngo_email || "")}</span></td>
        <td>${escapeHtml(p.title || "")}</td>
        <td>${escapeHtml(p.description || "")}</td>
        <td>৳${Number(p.goal || 0).toLocaleString()}</td>
        <td><span class="pill">${escapeHtml(p.status || "")}</span></td>
        <td>${escapeHtml(p.created_at || "")}</td>
        <td>
          <button class="btn" onclick="act(${p.id}, 'approved')">Approve</button>
          <button class="btn secondary" onclick="act(${p.id}, 'rejected')">Reject</button>
        </td>
      `;
      body.appendChild(tr);
    });

  }catch(e){
    body.innerHTML = `<tr><td colspan="8">Error: ${escapeHtml(String(e))}</td></tr>`;
  }
}

async function act(id, status){
  const res = await fetch("project_action.php", {
    method:"POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({ csrf: window.PHP_CSRF, id, status })
  });
  const data = await res.json();
  if(!data.success){
    alert(data.error || "Action failed");
    return;
  }
  loadPending();
}

function escapeHtml(s){
  return String(s)
    .replaceAll("&","&amp;")
    .replaceAll("<","&lt;")
    .replaceAll(">","&gt;")
    .replaceAll('"',"&quot;")
    .replaceAll("'","&#039;");
}

loadPending();
</script>
</body>
</html>


