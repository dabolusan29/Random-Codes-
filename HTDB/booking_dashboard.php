<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit;
}

$username = $_SESSION['username'];

// Fetch user's bookings with cancellation status
$sql = "
SELECT 
    b.*,
    COALESCE(
        (SELECT c.status FROM cancellations c WHERE c.booking_id = b.id ORDER BY c.id DESC LIMIT 1),
        'none'
    ) AS cancellation_status
FROM bookings b
WHERE b.username = ?
ORDER BY b.arrival_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Your Bookings</title>
<link rel="stylesheet" href="book.css">
<script>
    function handleAction(selectElement, bookingId) {
        const action = selectElement.value;
        if (!action) return;

        if (action === "cancel") {
            window.location.href = `booking_cancel_confirm.php?booking_id=${bookingId}`;
        } else if (action === "update") {
            window.location.href = `booking_update_dashboard.php?booking_id=${bookingId}`;
        }
        selectElement.selectedIndex = 0;
    }
</script>
</head>
<body>
<header>Hotel Management and Booking System</header>
<p class="back" style="text-align:center;">
        <a href="user_dashboard.php">🏠︎ Back to User Dashboard</a>
    </p>
<div class="container">
    <h1>𓆩𓆪 Your Bookings 𓆩𓆪</h1>

    <?php if (empty($bookings)): ?>
        <p class="no-bookings">You currently have no bookings.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Arrival Date</th>
                    <th>Suite Type</th>
                    <th>Booking Status</th>
                    <th>Cancellation</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['room_number']) ?></td>
                        <td><?= htmlspecialchars($b['arrival_date']) ?></td>
                        <td><?= htmlspecialchars($b['suite_type']) ?></td>
                        <td>
                            <span class="status 
                                <?= $b['accepted'] === null ? 'pending' : ($b['accepted'] == 1 ? 'accepted' : 'rejected') ?>">
                                <?= $b['accepted'] === null ? 'Pending' : ($b['accepted'] == 1 ? 'Accepted' : 'Rejected') ?>
                            </span>

                            <?php 
                                $updateStatus = strtolower($b['update_status'] ?? 'none');
                                if ($updateStatus !== 'none' && $updateStatus !== '') {
                                    $classMap = [
                                        'pending' => 'update-pending',
                                        'approved' => 'update-approved',
                                        'rejected' => 'update-rejected',
                                        'completed' => 'update-completed',
                                    ];
                                    $class = $classMap[$updateStatus] ?? 'update-pending';
                                    echo '<span class="status ' . $class . '" style="margin-left:10px;">Update: ' . ucfirst($updateStatus) . '</span>';
                                }
                            ?>
                        </td>
                        <td>
                            <?php 
                                switch ($b['cancellation_status']) {
                                    case 'pending': echo '<span class="status pending">Pending</span>'; break;
                                    case 'approved': echo '<span class="status accepted">Approved</span>'; break;
                                    case 'rejected': echo '<span class="status rejected">Rejected</span>'; break;
                                    case 'cancelled': echo '<span class="status cancelled">Cancelled</span>'; break;
                                    default: echo '<span class="status none">None</span>';
                                }
                            ?>
                        </td>
                        <td>
                            <select onchange="handleAction(this, <?= (int)$b['id'] ?>)">
                                <option value="">Select</option>
                                <option value="cancel">Cancel Booking</option>
                                <option value="update">Update Booking</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
