
<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../controllers/auth_controller.php";
$csrf = AuthController::csrfToken();

$BASE = "/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public";

// Not logged in → index
if (!isset($_SESSION["user"])) {
    header("Location: index.html");
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
    <link rel="stylesheet" type="text/css" href="<?php echo $BASE; ?>/assets/css/donor_style.css">

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
                    <h2 style="margin:0;">Welcome, <span id="donorName"><?php echo $payload['name']; ?></span></h2>
                    <div class="muted" style="margin-top:6px;">Email: <span id="donorEmail"><?php echo $payload['email']; ?></span></div>
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
            </div>
        </div>
    </div>

    <script>
    document.getElementById('logoutBtn').addEventListener('click', function() {
        // Redirect to logout.php which will destroy the session and redirect to login
        window.location.href = '/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/app/controllers/logout.php';
    });
</script>
</body>

</html>
