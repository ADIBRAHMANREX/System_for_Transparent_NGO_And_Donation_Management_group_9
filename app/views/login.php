<?php
require_once __DIR__ . "/../controllers/auth_controller.php";
$csrf = AuthController::csrfToken();

$BASE = "/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public";
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
   <a href="/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public" class="brand">Believe</a>

<main class="wrap">
  <h2>Login</h2>

  <div class="err" id="errBox"></div>

  <label>Email</label>
  <input type="email" id="email" autocomplete="username">

  <label>Password</label>
  <input type="password" id="password" autocomplete="current-password">

  <button class="btn" id="loginBtn" type="button">Login</button>

  <!-- ✅ MVC link -->
  <a class="btn2" href="register">Create account</a>


  <input type="hidden" id="csrf" value="<?= htmlspecialchars($csrf) ?>">
</main>

<script>
const errBox = document.getElementById("errBox");
function showErr(m){ errBox.style.display="block"; errBox.textContent=m; }
function clearErr(){ errBox.style.display="none"; errBox.textContent=""; }

// ✅ set your base once
const BASE = "/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public";

document.getElementById("loginBtn").addEventListener("click", async () => {
  clearErr();

  const payload = {
    csrf: document.getElementById("csrf").value,
    email: document.getElementById("email").value.trim(),
    password: document.getElementById("password").value
  };

  try {
    const res = await fetch(BASE + "/api/auth/login", {
      method: "POST",
      headers: {"Content-Type":"application/json"},
      body: JSON.stringify(payload)
    });

    // ✅ if route returns HTML/404, show useful error
    const ct = res.headers.get("content-type") || "";
    if (!ct.includes("application/json")) {
      const text = await res.text();
      return showErr("Login route returned non-JSON (likely 404). Check /api/auth/login in index.php.");
    }

    const data = await res.json();
    if (!data.success) return showErr(data.error || "Login failed.");

    const u = data.user;
    if (u.role === "admin") return window.location.href = BASE + "/admin";
    if (u.role === "ngo") {
      if (u.status !== "approved") return window.location.href = BASE + "/ngo/pending";
      return window.location.href = BASE + "/ngo";
    }
    return window.location.href = BASE + "/donor";

  } catch (e) {
    console.error(e);
    showErr("Login failed (network/route error). Check /api/auth/login route.");
  }
});
</script>

</body>
</html>


