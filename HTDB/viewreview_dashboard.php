<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit;
}

$username = $_SESSION['username'];

// Fetch all reviews
$result = $conn->query("SELECT * FROM reviews ORDER BY created_at DESC");
$reviews = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Calculate average rating
$avg_rating = 0;
if (count($reviews) > 0) {
    $total = 0;
    foreach ($reviews as $r) {
        $total += (int)$r['rating'];
    }
    $avg_rating = round($total / count($reviews));
}

// Function to censor username (show first letter, mask rest)
function censor_username($name) {
    $len = mb_strlen($name);
    if ($len <= 1) return $name;
    return mb_substr($name, 0, 1) . str_repeat('*', $len - 1);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>View All Reviews</title>
    <link rel="stylesheet" href="viewreview.css">
    <style>
        /* Back button styled as per your previous CSS */
    .back {
        text-align: center;
        margin: 25px auto;
    }

    .back a {
        display: inline-block;
        padding: 10px 24px;
        background-color: #004080;
        color: white;
        border-radius: 6px;
        font-weight: bold;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .back a:hover {
        background-color: #0066cc;
        text-decoration: none;
    }
    </style>
</head>
<body>
    <header>Hotel Management and Booking System</header>
    <div class="back">
            <a href="user_dashboard.php">🏠︎ Back to User Dashboard</a>
        </div>
    <div class="container">
        <h1>𓆩𓆪 Hotel Guest Reviews 𓆩𓆪</h1>

        <div class="average-rating">
            Average Rating: 
            <span class="stars" title="<?= $avg_rating ?> out of 5">
                <?php
                    echo str_repeat('★', $avg_rating);
                    echo str_repeat('☆', 5 - $avg_rating);
                ?>
            </span>
            (<?= $avg_rating ?> / 5)
        </div>

        <?php if (count($reviews) === 0): ?>
            <p>No reviews have been submitted yet.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div class="rating stars" title="Rating: <?= (int)$review['rating'] ?> out of 5">
                        <?php
                            $rating = (int)$review['rating'];
                            echo str_repeat('★', $rating);
                            echo str_repeat('☆', 5 - $rating);
                        ?>
                    </div>
                    <p><strong><?= htmlspecialchars(censor_username($review['username'])) ?>:</strong> <?= nl2br(htmlspecialchars($review['comment'])) ?></p>

                    <?php if (!empty($review['admin_reply'])): ?>
                        <div class="admin-reply">
                            <strong>Admin replied:</strong><br />
                            <?= nl2br(htmlspecialchars($review['admin_reply'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
