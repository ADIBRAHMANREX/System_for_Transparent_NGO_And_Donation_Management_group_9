<?php
declare(strict_types=1);

require_once __DIR__ . "/../controllers/auth_guard.php";
$me = require_login("donor"); 

$payload = [
  "name"  => $me["name"] ?? "Donor",
  "email" => $me["email"] ?? "",
  "role"  => $me["role"] ?? "donor",
];

$base = "/webtech_22-47887-2/System_for_Transparent_NGO_And_Donation_Management_group_10/public";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Donor Dashboard</title>
    <link rel="stylesheet" href="assets/css/donor_style.css" />
</head>
<body>

<div class="header">
    <div class="brand">Donor Dashboard</div>
    <div>
        <button id="profileBtn" class="btn">Profile</button>
        <button id="historyBtn" class="btn">History</button>
        <button id="projectsBtn" class="btn">Projects</button>
        <button id="rewardsBtn" class="btn">Rewards</button>
        <button id="recurringBtn" class="btn">Recurring Donations</button>
    </div>
</div>

<div class="container">
    
    <div class="section" id="profileSection">
        <h3>Welcome, <?php echo htmlspecialchars($payload['name']); ?></h3>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($payload['email']); ?></p>
        <div class="profile-card">
            <div>
                <p>Total Donated: ৳0</p>
                <p>Donations: 0</p>
                <p>Projects Supported: 0</p>
            </div>
            <button class="btn">Edit Profile</button>
        </div>
    </div>

    <div class="section" id="historySection" style="display: none;">
        <h3>Donation History</h3>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Amount Donated</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Project 1</td>
                    <td>৳100</td>
                    <td>2022-01-15</td>
                    <td>Completed</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section" id="projectsSection" style="display: none;">
        <h3>Projects</h3>
        <div class="project-container">
            <div class="project-card">
                <h4>Clean Water Initiative - Cumilla</h4>
                <p>NGO: Water for All</p>
                <p>Installing tube wells and filtration units in 8 villages.</p>
                <p>Raised: ৳220,000 / Goal: ৳400,000</p>
                <button class="btn">Donate</button>
            </div>
            <div class="project-card">
                <h4>Flood Relief - Noakhali</h4>
                <p>NGO: Hope Foundation</p>
                <p>Immediate relief for families affected by the August floods.</p>
                <p>Raised: ৳450,000 / Goal: ৳1,000,000</p>
                <button class="btn">Donate</button>
            </div>
        </div>
    </div>

    <div class="section" id="rewardsSection" style="display: none;">
        <h3>Rewards & Achievements</h3>
        <div class="reward-card">
            <h4>Top Donor of the Month</h4>
            <p>You donated ৳500 this month!</p>
        </div>
    </div>

    <div class="section" id="recurringSection" style="display: none;">
        <h3>Recurring Donations</h3>
        <p>Set up your recurring donation amount:</p>
        <input type="number" id="recurringAmount" placeholder="Amount" />
        <button class="btn">Set Up Recurring Donation</button>
    </div>
</div>


<script src="assets/js/donor_script.js"></script>

<script>
    
    function showSection(sectionId) {
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => section.style.display = 'none');
        document.getElementById(sectionId).style.display = 'block';
    }

    
    document.getElementById('profileBtn').onclick = () => showSection('profileSection');
    document.getElementById('historyBtn').onclick = () => showSection('historySection');
    document.getElementById('projectsBtn').onclick = () => showSection('projectsSection');
    document.getElementById('rewardsBtn').onclick = () => showSection('rewardsSection');
    document.getElementById('recurringBtn').onclick = () => showSection('recurringSection');

    
    showSection('profileSection');
</script>

</body>
</html>
