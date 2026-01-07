<?php
require_once __DIR__ . "/../controllers/auth_controller.php";
require_once __DIR__ . "/../controllers/auth_guard.php";

$csrf = AuthController::csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Login</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f5f6f7;margin:0}
    .wrap{max-width:420px;margin:28px auto;padding:18px;background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    input{width:100%;padding:10px;border-radius:8px;border:1px solid #cfcfcf}
    label{display:block;margin:10px 0 6px}
    .btn{width:100%;padding:10px;border:none;border-radius:10px;background:#111;color:#fff;cursor:pointer;margin-top:12px}
    .btn2{display:block;text-align:center;text-decoration:none;margin-top:10px;background:#eee;color:#111;padding:10px;border-radius:10px}
    .err{display:none;background:#ffe8e8;border:1px solid #ffb3b3;color:#8a1f1f;padding:10px;border-radius:10px;margin:10px 0}
  </style>
</head>
<body>
<main class="wrap">
  <h2>Login</h2>

  <div class="err" id="errBox"></div>

  <label>Email</label>
  <input type="email" id="email">

  <label>Password</label>
  <input type="password" id="password">

  <button class="btn" id="loginBtn" type="button">Login</button>
  <a class="btn2" href="register.php">Create account</a>

  <input type="hidden" id="csrf" value="<?= htmlspecialchars($csrf) ?>">
</main>

<script>
const errBox = document.getElementById("errBox");
function showErr(m){ errBox.style.display="block"; errBox.textContent=m; }
function clearErr(){ errBox.style.display="none"; errBox.textContent=""; }

document.getElementById("loginBtn").addEventListener("click", async () => {
  clearErr();
  const payload = {
    csrf: document.getElementById("csrf").value,
    email: document.getElementById("email").value.trim(),
    password: document.getElementById("password").value
  };

  const res = await fetch("auth_login.php", {
  method: "POST",
  headers: {"Content-Type":"application/json"},
  credentials: "same-origin",
  body: JSON.stringify(payload)
});


  const data = await res.json();
  if(!data.success) return showErr(data.error || "Login failed.");

  const u = data.user;

  if(u.role === "admin") return window.location.href = "admin_dashboard.php";

  if(u.role === "ngo"){
    if(u.status !== "approved") return window.location.href = "ngo_pending.php";
    return window.location.href = "ngo.php";
  }

  return window.location.href = "donor_dashboard.php";
});
</script>
</body>
</html>

