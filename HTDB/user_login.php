<?php
session_start();
require 'config.php';

if (isset($_SESSION['username'])) {
    header("Location: user_dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please fill in both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_id'] = $user['id'];
                    header("Location: user_dashboard.php");
                    exit;
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Invalid username or password.";
            }
            $stmt->close();
        } else {
            $error = "Database error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>User Login - Hotel Management System</title>
<link rel="stylesheet" href="login.css">
<script>
     function togglePassword(id) {
        const input = document.getElementById(id);
        const toggleBtn = event.target;
        if (input.type === 'password') {
            input.type = 'text';
            toggleBtn.textContent = 'Hide';
        } else {
            input.type = 'password';
            toggleBtn.textContent = 'Show';
        }
    }
</script>
</head>
<body>

<header>Hotel Management and Booking System</header>

<div class="login-box">
    <h2>🔐 User Login</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" novalidate>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required autofocus>

        <label for="password">Password:</label>
        <div class="input-group">
            <input type="password" name="password" id="password" required>
            <button type="button" class="toggle" onclick="togglePassword('password')" aria-label="Show or hide password">Show</button>
        </div>

        <button type="submit">Login</button>
    </form>

    <div class="link">
        <a href="user_selection.php">🏠︎ Back to User Selection</a>
    </div>
</div>

</body>
</html>
