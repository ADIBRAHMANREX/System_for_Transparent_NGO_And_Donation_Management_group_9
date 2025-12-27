<?php
require_once __DIR__ . "/auth_controller.php";
require_once __DIR__ . "/user_model.php";

AuthController::startSession();
$me = $_SESSION["user"] ?? null;

if(!$me || ($me["role"] ?? "") !== "admin"){
  header("Location: login.php");
  exit;
}

if($_SERVER["REQUEST_METHOD"] === "POST"){
  $id = (int)($_POST["id"] ?? 0);
  $status = (string)($_POST["status"] ?? "pending");
  if($id > 0 && in_array($status, ["approved","rejected","pending"], true)){
    UserModel::setStatus($id, $status);
  }
  header("Location: admin_dashboard.php");
  exit;
}

$ngos = UserModel::listNGOs();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Admin Dashboard</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f5f6f7;margin:0}
    .wrap{max-width:900px;margin:20px auto;background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
    .btn{padding:8px 10px;border:none;border-radius:8px;background:#111;color:#fff;cursor:pointer}
    .btn.secondary{background:#eee;color:#111}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
    a{color:#111;text-decoration:none}
  </style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <h2>NGO Approvals</h2>
    <a class="btn secondary" href="logout.php">Logout</a>
  </div>

  <table>
    <thead>
      <tr>
        <th>Name</th><th>Email</th><th>Status</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($ngos as $ngo): ?>
        <tr>
          <td><?= htmlspecialchars($ngo["first_name"]." ".$ngo["last_name"]) ?></td>
          <td><?= htmlspecialchars($ngo["email"]) ?></td>
          <td><?= htmlspecialchars($ngo["status"]) ?></td>
          <td>
            <form method="post" style="display:inline">
              <input type="hidden" name="id" value="<?= (int)$ngo["id"] ?>">
              <input type="hidden" name="status" value="approved">
              <button class="btn" type="submit">Approve</button>
            </form>
            <form method="post" style="display:inline;margin-left:6px">
              <input type="hidden" name="id" value="<?= (int)$ngo["id"] ?>">
              <input type="hidden" name="status" value="rejected">
              <button class="btn secondary" type="submit">Reject</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>

      <?php if(count($ngos) === 0): ?>
        <tr><td colspan="4">No NGO accounts found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
