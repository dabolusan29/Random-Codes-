<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit;
}

$message = "";       // Initialize to avoid undefined variable warning
$message_class = ""; // Initialize to avoid undefined variable warning
$show_toast = false; // Flag to trigger toast display in JS

$username = $_SESSION['username'];

// Function to determine suite type based on room number
function getSuiteType($room_number) {
    $room_number = (int)$room_number;
    if ($room_number >= 101 && $room_number <= 110) return "Standard";
    if ($room_number >= 201 && $room_number <= 210) return "Deluxe";
    if ($room_number >= 301 && $room_number <= 310) return "VIP Suite";
    return "Unknown";
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = trim($_POST['room_number'] ?? '');
    $arrival_date = $_POST['arrival_date'] ?? '';

    if ($room_number && $arrival_date) {
        $suite_type = getSuiteType($room_number);

        if ($suite_type === "Unknown") {
            $message = "Invalid room number selected.";
            $message_class = "error";
        } else {
            // Check if the room is already booked on the same date
            $check = $conn->prepare("SELECT id FROM bookings WHERE room_number = ? AND arrival_date = ?");
            $check->bind_param("ss", $room_number, $arrival_date);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $message = "Room $room_number is already booked on $arrival_date. Please choose another room.";
                $message_class = "error";
            } else {
                $stmt = $conn->prepare("INSERT INTO bookings (username, room_number, arrival_date, suite_type) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssss", $username, $room_number, $arrival_date, $suite_type);
                    $stmt->execute();
                    $stmt->close();

                    // Log user activity
                    $activity = "Booked room $room_number on $arrival_date ($suite_type)";
                    $log = $conn->prepare("INSERT INTO users_activity (username, activity) VALUES (?, ?)");
                    $log->bind_param("ss", $username, $activity);
                    $log->execute();
                    $log->close();

                    $message = "Booking submitted successfully!";
                    $message_class = "success";
                    $show_toast = true; // trigger toast for success
                } else {
                    $message = "Error: Could not submit booking.";
                    $message_class = "error";
                }
            }
            $check->close();
        }
    } else {
        $message = "Please fill in all fields.";
        $message_class = "error";
    }
}

// Generate categorized room numbers
$rooms = [
    "Standard Suite" => range(101, 110),
    "Deluxe Suite"   => range(201, 210),
    "VIP Suite"      => range(301, 310)
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book a Hotel Room</title>
    <link rel="stylesheet" href="booking.css">
    <script>
        window.onload = function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('arrival_date').setAttribute('min', today);

            <?php if ($show_toast): ?>
            // Show toast notification for 3 seconds
            const toast = document.getElementById("toast");
            toast.classList.add("show");
            setTimeout(() => {
                toast.classList.remove("show");
            }, 3000);
            <?php endif; ?>
        };
    </script>
</head>
<header> Hotel Management and Booking System </header>
<body>
<div class="container">
    <h2>𓆩𓆪 Book a Room 𓆩𓆪</h2>

    <!-- Keep error messages visible in the page (non-success) -->
    <?php if ($message && $message_class === 'error'): ?>
        <div class="message error">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="room_number">Room Number</label>
        <select name="room_number" id="room_number" required>
            <option value="">-- Select Room --</option>
            <?php foreach ($rooms as $suite => $numbers): ?>
                <optgroup label="<?= htmlspecialchars($suite) ?>">
                    <?php foreach ($numbers as $room): ?>
                        <option value="<?= htmlspecialchars($room) ?>"><?= htmlspecialchars($room) ?> (<?= htmlspecialchars($suite) ?>)</option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>

        <label for="arrival_date">Arrival Date</label>
        <input type="date" name="arrival_date" id="arrival_date" required>

        <button type="submit">Submit Booking</button>
    </form>

    <div class="back">
        <a href="user_dashboard.php">🏠︎ Back to User Dashboard</a>
    </div>
</div>

<!-- Toast container -->
<div id="toast"><?= htmlspecialchars($message) ?></div>

</body>
</html>
