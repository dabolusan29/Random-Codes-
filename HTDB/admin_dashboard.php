<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_username = $_SESSION['admin_username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Selection</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<header>Hotel Management and Booking System - 👨🏻‍💼 Admin Dashboard</header>

<div class="dashboard-container">
    <h1>✦ Welcome, <?= htmlspecialchars($admin_username) ?>! ✦</h1>

    <div class="dashboard-grid">
        <div class="dashboard-item">
            <a href="calendar_dashboard.php">🛏️ Booking Calendar & Status</a>
        </div>
        <div class="dashboard-item">
            <a href="approval_dashboard.php">✔️❌ Approve or Reject Bookings</a>
        </div>
        <div class="dashboard-item">
            <a href="cancelation_dashboard.php">❗❗ Approve Cancellations</a>
        </div>
        <div class="dashboard-item">
            <a href="update_dashboard.php">🛠 Approve Booking Updates</a>
        </div>
        <div class="dashboard-item">
            <a href="adminactivity_dashboard.php">👨🏻‍💻 View Admin Activity Logs</a>
        </div>
        <div class="dashboard-item">
            <a href="reviewreply_dashboard.php">⭐💬 Manage User Reviews</a>
        </div>
    </div>

    <div class="logout-link">
        <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">🏃🚪 Logout</a>
    </div>
</div>

</body>
</html>
