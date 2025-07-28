<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit;
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - Selection</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<header>Hotel Management and Booking System - 🙍🏻‍♂️ User Dashboard</header>

<div class="dashboard-container">
    <h1>✦ Welcome, <span style="color:#3c72b8;"><?= htmlspecialchars($username) ?></span>! ✦</h1>

    <div class="dashboard-grid">
        <div class="dashboard-item">
            <a href="booking_book_dashboard.php">🛏️ Book a Room</a>
        </div>
        <div class="dashboard-item">
            <a href="booking_dashboard.php">📝 My Bookings</a>
        </div>
        <div class="dashboard-item">
            <a href="sendreview_dashboard.php">🌟 Submit Review</a>
        </div>
        <div class="dashboard-item">
            <a href="viewreview_dashboard.php">👀 View All Reviews</a>
        </div>
        <div class="dashboard-item">
            <a href="useractivity_dashboard.php">💻 My Activity</a>
        </div>
    </div>

    <div class="logout-link">
        <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">🏃🚪 Logout</a>
    </div>
</div>

</body>
</html>
