<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit;
}

$username = $_SESSION['username'];
$success = '';
$error = '';
$booking = null;

if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    die("Invalid booking ID.");
}

$booking_id = (int)$_GET['booking_id'];

// Fetch booking details for display
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND username = ?");
$stmt->bind_param("is", $booking_id, $username);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    die("Booking not found or access denied.");
}

// Suite types mapping
function getSuiteType($room) {
    $room = (int)$room;
    if ($room >= 101 && $room <= 110) return "Standard";
    if ($room >= 201 && $room <= 210) return "Deluxe";
    if ($room >= 301 && $room <= 310) return "VIP Suite";
    return "Unknown";
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newRoom = $_POST['room_number'] ?? '';
    $newDate = $_POST['arrival_date'] ?? '';
    $note = $_POST['note'] ?? '';
    $newSuite = getSuiteType($newRoom);

    if ($newRoom && $newDate && $newSuite !== "Unknown") {
        // Check if room is available for the new date
        $check = $conn->prepare("SELECT id FROM bookings WHERE room_number = ? AND arrival_date = ? AND id != ?");
        $check->bind_param("ssi", $newRoom, $newDate, $booking_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Room $newRoom is already booked on $newDate. Please choose another room.";
        } else {
            // Check for existing update request
            $stmt = $conn->prepare("SELECT * FROM update_requests WHERE booking_id = ? AND status = 'pending'");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($existing) {
                $error = "You already have a pending update request.";
            } else {
                // Insert update request
                $stmt = $conn->prepare("INSERT INTO update_requests (username, room_number, arrival_date, suite_type, note, status, booking_id) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
                $stmt->bind_param("sssssi", $username, $newRoom, $newDate, $newSuite, $note, $booking_id);
                $stmt->execute();
                $stmt->close();

                // Update booking update_status
                $stmt = $conn->prepare("UPDATE bookings SET update_status = 'pending' WHERE id = ?");
                $stmt->bind_param("i", $booking_id);
                $stmt->execute();
                $stmt->close();

                // Log activity
                $activity = "Requested update for booking ID $booking_id";
                $stmt = $conn->prepare("INSERT INTO users_activity (username, activity) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $activity);
                $stmt->execute();
                $stmt->close();

                $success = "Update request submitted successfully.";
                header("Refresh: 2; url=booking_dashboard.php");
            }
        }
        $check->close();
    } else {
        $error = "Please select a valid room and date.";
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
    <title>Update Booking Request</title>
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
        }
        h2 {
            color: #004080;
            text-align: center;
            margin-bottom: 20px;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }
        textarea {
            min-height: 80px;
        }
        button {
            width: 100%;
            background: #004080;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.25s ease;
        }
        button:hover {
            background: #0066cc;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 6px;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #842029;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            text-decoration: none;
            color: #004080;
            font-weight: bold;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        textarea {
    width: 100%;
    padding: 10px;
    margin: 12px 0;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    min-height: 100px;  /* Consistent height */
    resize: vertical;   /* Allow vertical resize only */
    box-sizing: border-box;
}

    </style>
    <script>
        function confirmUpdate() {
            return confirm("Are you sure you want to submit this update request?");
        }
        window.onload = function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('arrival_date').setAttribute('min', today);
        };
    </script>
</head>
<body>
<header>Hotel Management and Booking System</header>
<div class="container">
    <h2>𓆩𓆪 Update Booking Request 𓆩𓆪</h2>

    <?php if ($success): ?>
        <p class="message success"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p class="message error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" onsubmit="return confirmUpdate()">
        <label>New Room Number</label>
        <select name="room_number" required>
            <option value="">-- Select Room --</option>
            <?php foreach ($rooms as $suite => $numbers): ?>
                <optgroup label="<?= $suite ?>">
                    <?php foreach ($numbers as $room): ?>
                        <option value="<?= $room ?>" <?= $booking['room_number'] == $room ? 'selected' : '' ?>>
                            <?= $room ?> (<?= $suite ?>)
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>

        <label>New Arrival Date</label>
        <input type="date" name="arrival_date" id="arrival_date" value="<?= htmlspecialchars($booking['arrival_date']) ?>" required>

        <label>Note (optional)</label>
        <textarea name="note" placeholder="Any special notes or reason for the update..."></textarea>

        <button type="submit">Submit Update Request</button>
    </form>

    <div class="back-link">
        <a href="booking_dashboard.php">🏠︎ Back to Booking Dashboard</a>
    </div>
</div>
</body>
</html>
