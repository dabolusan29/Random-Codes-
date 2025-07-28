<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit;
}

$username = $_SESSION['username'];
$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$message = '';
$error = '';

// Fetch booking details for confirmation
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND username = ?");
$stmt->bind_param("is", $bookingId, $username);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    $error = "Booking not found or you don't have permission to cancel this booking.";
} else {
    if ($booking['accepted'] != 1) {
        $error = "Only accepted bookings can be cancelled.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $stmt = $conn->prepare("SELECT * FROM cancellations WHERE booking_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        $error = "You already have a pending cancellation request for this booking.";
    } else {
        $stmt = $conn->prepare("INSERT INTO cancellations (username, room_number, arrival_date, suite_type, booking_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssis", $username, $booking['room_number'], $booking['arrival_date'], $booking['suite_type'], $bookingId);
        if ($stmt->execute()) {
            $stmt->close();

            $activity = "Requested cancellation for booking ID $bookingId";
            $stmt = $conn->prepare("INSERT INTO users_activity (username, activity) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $activity);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Cancellation request sent successfully.";
            header("Location: booking_dashboard.php");
            exit;
        } else {
            $error = "Failed to send cancellation request. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Confirm Cancellation</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f0f4f8;
        max-width: 600px;
        margin: 30px auto;
        padding: 0 15px;
    }
       header {
        background: linear-gradient(135deg, #1f3a93, #3c72b8);
        color: white;
        padding: 18px 40px;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 1.1px;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        margin-bottom: 30px;
        border-radius: 8px;
        text-align: center;
    }
    .container {
        background: white;
        padding: 25px 30px;
        border-radius: 8px;
        box-shadow: 0 0 8px rgba(0,0,0,0.1);
        text-align: center;
    }
    h2 {
        color: #004080;
        margin-bottom: 20px;
    }
    p {
        font-size: 1.1em;
        margin: 10px 0;
        color: #333;
    }
    .error {
        background: #f8d7da;
        color: #842029;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 6px;
        font-weight: 600;
    }
    button {
        background: #dc3545;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1em;
        transition: background 0.25s ease;
        margin-top: 20px;
    }
    button:hover {
        background: #a71d2a;
    }
    .cancel-link {
        display: inline-block;
        margin-top: 20px;
        color: #004080;
        text-decoration: none;
        font-weight: bold;
    }
    .cancel-link:hover {
        text-decoration: underline;
    }
</style>
<header>Hotel Management and Booking System</header>
<script>
    function confirmCancel() {
        return confirm("Are you sure you want to send a cancellation request for this booking?");
    }
</script>
</head>
<body>
<div class="container">
    <h2>𓆩𓆪 Confirm Cancellation 𓆩𓆪</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <a class="cancel-link" href="booking_dashboard.php">🏠︎ Back to Bookings</a>
    <?php else: ?>
        <p>Are you sure you want to request cancellation for this booking?</p>
        <p><strong>Room Number:</strong> <?= htmlspecialchars($booking['room_number']) ?></p>
        <p><strong>Arrival Date:</strong> <?= htmlspecialchars($booking['arrival_date']) ?></p>
        <p><strong>Suite Type:</strong> <?= htmlspecialchars($booking['suite_type']) ?></p>

        <form method="POST" onsubmit="return confirmCancel()">
            <button type="submit">Yes, Request Cancellation</button>
        </form>

        <a class="cancel-link" href="booking_dashboard.php">🔙 No, Go Back</a>
    <?php endif; ?>
</div>
</body>
</html>
