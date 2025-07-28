<?php
session_start();
require 'config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username)) $errors[] = "Username is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "Username or email already exists.";
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        if ($stmt->execute()) $success = true;
        else $errors[] = "Failed to register user. Please try again.";
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>User Sign Up - Hotel Management System</title>
    <link rel="stylesheet" href="signup.css">
    <?php if ($success): ?>
    <meta http-equiv="refresh" content="4;url=user_login.php" />
    <?php endif; ?>

    <script>
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'Hide';
            } else {
                input.type = 'password';
                btn.textContent = 'Show';
            }
        }
    </script>
</head>
<body>

<header>Hotel Management and Booking System</header>

<div class="container">
    <h1>🔓 User Sign Up</h1>

    <?php if ($success): ?>
        <div class="message success">
            Successfully signed up! Redirecting to login page...
        </div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="message error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label for="password">Password</label>
            <div class="input-group">
                <input type="password" id="password" name="password" required>
                <button type="button" class="toggle" onclick="togglePassword('password', this)" aria-label="Show or hide password">Show</button>
            </div>

            <label for="confirm_password">Confirm Password</label>
            <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" required>
                <button type="button" class="toggle" onclick="togglePassword('confirm_password', this)" aria-label="Show or hide password">Show</button>
            </div>

            <button type="submit">Sign Up</button>
        </form>
    <?php endif; ?>

    <a href="user_selection.php">🏠︎ Back to User Selection</a>
</div>

</body>
</html>

