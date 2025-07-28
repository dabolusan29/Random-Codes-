<?php
session_start();
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Database error: Unable to prepare login query.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Login - Hotel Management System</title>
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
    <h1>🔐 Admin Login</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" novalidate>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required autofocus>

        <label for="password">Password:</label>
        <div class="input-group">
            <input type="password" name="password" id="password" required>
            <button type="button" class="toggle" onclick="togglePassword('password')">Show</button>
        </div>

        <button type="submit">Login</button>
    </form>

    <div class="link">
        <a href="admin_selection.php">🏠︎ Back to Admin Selection</a>
    </div>
</div>

</body>
</html>
