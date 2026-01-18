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
  <title>Register</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f5f6f7;margin:0}
    .wrap{max-width:460px;margin:30px auto;padding:20px;background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    input,select{width:100%;padding:10px;border-radius:8px;border:1px solid #cfcfcf}
    label{display:block;margin:10px 0 6px}
    .btn{width:100%;padding:10px;border:none;border-radius:10px;background:#111;color:#fff;cursor:pointer;margin-top:14px}
    .btn2{display:block;text-align:center;text-decoration:none;margin-top:10px;background:#eee;color:#111;padding:10px;border-radius:10px}
    .hidden{display:none}
    .err{display:none;background:#ffe8e8;border:1px solid #ffb3b3;color:#8a1f1f;padding:10px;border-radius:10px;margin:10px 0}
  </style>
</head>
<body>
<main class="wrap">
  <h2>Create Account</h2>

  <div class="err" id="errBox"></div>

  <label>Sign up as</label>
  <select id="role" required>
    <option value="">Select role</option>
    <option value="donor">Donor</option>
    <option value="ngo">NGO</option>
  </select>

  <div id="commonFields" class="hidden">
    <label>First Name</label>
    <input type="text" id="first_name" required>

    <label>Last Name</label>
    <input type="text" id="last_name" required>

    <label>Email</label>
    <input type="email" id="email" required>

    <label>Password</label>
    <input type="password" id="password" required>
  </div>

  <div id="donorFields" class="hidden">
    <label>Date of Birth</label>
    <input type="date" id="dob">
  </div>

  <div id="ngoFields" class="hidden">
    <label>NGO Name</label>
    <input type="text" id="ngo_name">

    <label>NGO License Number</label>
    <input type="text" id="ngo_license">
  </div>

  <button class="btn hidden" id="registerBtn" type="button">Register</button>
  <a class="btn2" href="login">Back to Login</a>

  <input type="hidden" id="csrf" value="<?= htmlspecialchars($csrf) ?>">
</main>

<script>
const errBox = document.getElementById("errBox");
function showErr(m){ errBox.style.display="block"; errBox.textContent=m; }
function clearErr(){ errBox.style.display="none"; errBox.textContent=""; }

const roleSelect = document.getElementById("role");
const commonFields = document.getElementById("commonFields");
const donorFields = document.getElementById("donorFields");
const ngoFields = document.getElementById("ngoFields");
const registerBtn = document.getElementById("registerBtn");

function hideAll(){
  commonFields.classList.add("hidden");
  donorFields.classList.add("hidden");
  ngoFields.classList.add("hidden");
  registerBtn.classList.add("hidden");
}
hideAll();

roleSelect.addEventListener("change", () => {
  clearErr();
  hideAll();
  if(roleSelect.value === "donor"){
    commonFields.classList.remove("hidden");
    donorFields.classList.remove("hidden");
    registerBtn.classList.remove("hidden");
  }
  if(roleSelect.value === "ngo"){
    commonFields.classList.remove("hidden");
    ngoFields.classList.remove("hidden");
    registerBtn.classList.remove("hidden");
  }
});

document.getElementById("registerBtn").addEventListener("click", async () => {
  clearErr();

  const role = roleSelect.value;
  if(!role) return showErr("Please select role.");

  const payload = {
    csrf: document.getElementById("csrf").value,
    role,
    first_name: document.getElementById("first_name").value.trim(),
    last_name: document.getElementById("last_name").value.trim(),
    email: document.getElementById("email").value.trim(),
    password: document.getElementById("password").value,
    dob: document.getElementById("dob").value,
    ngo_name: document.getElementById("ngo_name").value.trim(),
    ngo_license: document.getElementById("ngo_license").value.trim()
  };

  // role-based required checks
  if(!payload.first_name || !payload.last_name || !payload.email || !payload.password){
    return showErr("Please fill all required fields.");
  }
  if(role === "ngo" && (!payload.ngo_name || !payload.ngo_license)){
    return showErr("NGO name and license are required.");
  }

  const res = await fetch("api/auth/register", {
  method: "POST",
  headers: {"Content-Type":"application/json"},
  credentials: "same-origin",
  body: JSON.stringify(payload)
});

  

  const data = await res.json();
  if(!data.success) return showErr(data.error || "Registration failed.");

  // success
  if(role === "ngo"){
    alert("NGO registered! Wait for admin approval.");
  } else {
    alert("Account created! Please login.");
  }
  window.location.href = "login.php";
});
</script>
</body>
</html>


