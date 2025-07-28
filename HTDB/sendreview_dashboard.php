<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit;
}

$username = $_SESSION['username'];
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['comment'])) {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO reviews (username, rating, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $username, $rating, $comment);
        $stmt->execute();
        $stmt->close();
        $success = "✅ Review submitted successfully!";
    } else {
        $success = "⚠️ Please enter a valid rating and comment.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Send Review</title>
    <link rel="stylesheet" href="sendreview.css">
</head>
<header>Hotel Management and Booking System</header>
<body>
    <div class="review-box">
        <h2>𓆩𓆪 Leave a Review 𓆩𓆪</h2>
        <form method="POST" id="reviewForm">
            <div class="stars">
                <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
            </div>

            <textarea name="comment" placeholder="Write your review here..." required></textarea>
            <button class="submit-btn" type="submit">Submit Review</button>
        </form>

        <?php if (!empty($success) && strpos($success, 'successfully') !== false): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="error"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="back">
            <a href="user_dashboard.php">🏠︎ Back to User Dashboard</a>
        </div>
    </div>

    <div id="toast">Your review has been sent successfully!</div>

    <script>
        window.onload = function() {
            const successMessage = <?= json_encode($success) ?>;
            if (successMessage && successMessage.includes('successfully')) {
                const toast = document.getElementById('toast');
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
        };
    </script>
</body>
</html>
