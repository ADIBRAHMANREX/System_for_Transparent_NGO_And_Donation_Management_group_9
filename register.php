<?php
require_once __DIR__ . "/auth_controller.php";
$csrf = AuthController::csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Register</title>
  <link rel="stylesheet" href="register.css"/>
</head>
<body>
<main class="reg-wrap">
  <section class="reg-card">
    <h2>Create account</h2>

    <div class="reg-steps">
      <div class="step-dot active" data-step="1"></div>
      <div class="step-dot" data-step="2"></div>
      <div class="step-dot" data-step="3"></div>
    </div>

    <p class="reg-sub" id="regSub">Let’s start with your details.</p>

    <div class="reg-error" id="regError" style="display:none;"></div>

    <div class="reg-step" id="step1">
      <label>First name</label>
      <input id="first_name" type="text" placeholder="First name"/>

      <label>Last name</label>
      <input id="last_name" type="text" placeholder="Last name"/>

      <label>Email</label>
      <input id="email" type="email" placeholder="you@email.com"/>
      <small id="emailHint"></small>

      <button class="btn" id="next1" type="button">Continue</button>
      <p class="reg-link">Already have an account? <a href="login.php">Login</a></p>
    </div>

    <div class="reg-step" id="step2" style="display:none;">
      <label>Password</label>
      <input id="password" type="password" placeholder="Min 8 characters"/>

      <label>Confirm password</label>
      <input id="password2" type="password" placeholder="Repeat password"/>

      <div class="reg-actions">
        <button class="btn secondary" id="back2" type="button">Back</button>
        <button class="btn" id="next2" type="button">Continue</button>
      </div>
    </div>

    <div class="reg-step" id="step3" style="display:none;">
      <label>Select role</label>

      <div class="role-grid">
        <button class="role-card" data-role="donor" type="button">
          <strong>Donor</strong>
          <span>Donate and track your donations</span>
        </button>

        <button class="role-card" data-role="ngo" type="button">
          <strong>NGO</strong>
          <span>Submit projects (requires admin approval)</span>
        </button>
      </div>

      <div class="reg-actions">
        <button class="btn secondary" id="back3" type="button">Back</button>
        <button class="btn" id="submitBtn" type="button">Create account</button>
      </div>

      <p class="reg-link"><a href="login.php">Back to login</a></p>
    </div>

    <input type="hidden" id="csrf" value="<?= htmlspecialchars($csrf) ?>">
  </section>
</main>

<script src="register.js"></script>
</body>
</html>
