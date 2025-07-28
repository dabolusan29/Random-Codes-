<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit;
}

$username = $_SESSION['username'];

// Auto-delete reviews older than 30 days for this user
$deleteOld = $conn->prepare("DELETE FROM reviews WHERE username = ? AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
$deleteOld->bind_param("s", $username);
$deleteOld->execute();
$deleteOld->close();

$reviewDeleted = false;

// Handle delete review request via GET param 'delete_id'
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    // Verify review belongs to this user before deleting
    $checkStmt = $conn->prepare("SELECT id FROM reviews WHERE id = ? AND username = ?");
    $checkStmt->bind_param("is", $delete_id, $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $delStmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $delStmt->bind_param("i", $delete_id);
        $delStmt->execute();
        $delStmt->close();
        $reviewDeleted = true;
    }
    $checkStmt->close();

    // Redirect with query param to show toast notification
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?deleted=1");
    exit;
}

// Fetch user booking history
$bookings = $conn->query("SELECT * FROM bookings WHERE username = '$username' ORDER BY arrival_date DESC")->fetch_all(MYSQLI_ASSOC);

// Fetch user cancellations
$cancellations = $conn->query("SELECT * FROM cancellations WHERE username = '$username' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Fetch user reviews
$reviews = $conn->query("SELECT * FROM reviews WHERE username = '$username' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Helper function to get CSS class for status
function statusClass($status) {
    $status = strtolower($status);
    return match ($status) {
        'accepted' => 'status-accepted',
        'pending' => 'status-pending',
        'rejected' => 'status-rejected',
        'approved' => 'status-approved',
        default => 'status-unknown',
    };
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Activity Log</title>
    <link rel="stylesheet" href="useractivity.css">
    <script>
        function confirmDelete(reviewId) {
            if (confirm("Are you sure you want to delete this review?")) {
                window.location.href = "?delete_id=" + reviewId;
            }
        }

        function showToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        window.onload = function() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('deleted') === '1') {
                showToast();
                if (history.replaceState) {
                    let url = new URL(window.location);
                    url.searchParams.delete('deleted');
                    history.replaceState(null, '', url.toString());
                }
            }
        }
    </script>
</head>
<body>
    <header>Hotel Management and Booking System</header>

    <div class="back-container">
        <a href="user_dashboard.php" class="back-link">🏠︎ Back to Dashboard</a>
    </div>

    <div class="welcome-container">
        ✦ Welcome, <?= htmlspecialchars($username) ?>! Here's your Activity ✦
    </div>

    <div class="section">
        <h2>𓆩𓆪 Bookings 𓆩𓆪</h2>
        <?php if (count($bookings) > 0): ?>
            <table>
                <tr><th>Room Number</th><th>Suite Type</th><th>Arrival Date</th><th>Status</th></tr>
                <?php foreach ($bookings as $b): ?>
                    <?php 
                        $status_text = (isset($b['accepted']) && $b['accepted']) ? 'Accepted' : 'Pending';
                        $status_class = statusClass($status_text);
                        $suite_display = ($b['suite_type'] === "0" || $b['suite_type'] === "") ? "Not Indicated Suite" : htmlspecialchars($b['suite_type']);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($b['room_number']) ?></td>
                        <td><?= $suite_display ?></td>
                        <td><?= htmlspecialchars($b['arrival_date']) ?></td>
                        <td class="<?= $status_class ?>"><?= $status_text ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>You have no bookings yet.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>𓆩𓆪 Cancellations 𓆩𓆪</h2>
        <?php if (count($cancellations) > 0): ?>
            <table>
                <tr><th>Room Number</th><th>Suite Type</th><th>Date</th><th>Status</th></tr>
                <?php foreach ($cancellations as $c): ?>
                    <?php
                        $status_class = statusClass($c['status']);
                        $suite_display = ($c['suite_type'] === "0" || $c['suite_type'] === "") ? "Not Indicated Suite" : htmlspecialchars($c['suite_type']);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($c['room_number']) ?></td>
                        <td><?= $suite_display ?></td>
                        <td><?= htmlspecialchars($c['arrival_date']) ?></td>
                        <td class="<?= $status_class ?>"><?= htmlspecialchars($c['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No cancellations requested.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>𓆩𓆪 Reviews 𓆩𓆪</h2>
        <?php if (count($reviews) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Admin Reply</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td class="star-rating">
                                <?php
                                    $stars = (int)$r['rating'];
                                    echo str_repeat('★', $stars);
                                    echo str_repeat('☆', 5 - $stars);
                                ?>
                            </td>
                            <td><?= nl2br(htmlspecialchars($r['comment'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($r['admin_reply'])) ?></td>
                            <td><?= htmlspecialchars($r['created_at']) ?></td>
                            <td>
                                <button class="delete-btn" onclick="confirmDelete(<?= (int)$r['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You haven't submitted any reviews yet.</p>
        <?php endif; ?>
    </div>

    <div id="toast">✅ Review deleted successfully!</div>
</body>
</html>
