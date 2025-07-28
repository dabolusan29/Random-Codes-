<?php
require 'config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $admin_code = $_POST['admin_code'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($admin_code)) {
        $errors[] = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } elseif ($admin_code !== 'admin1234567890') {
        $errors[] = "Invalid admin code.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (!preg_match('/@gmail\.com$/', $email)) {
        $errors[] = "Email must be a Gmail address (e.g., example@gmail.com).";
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $errors[] = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                if ($stmt->execute()) {
                    $success = "Admin registered successfully! Redirecting to login...";
                } else {
                    $errors[] = "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "Prepare failed: " . $conn->error;
            }
        }
        $checkStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Signup - Hotel Management System</title>
<link rel="stylesheet" href="signup.css">
<?php if ($success): ?>
    <meta http-equiv="refresh" content="4;url=admin_login.php" />
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
    <h1>🔓 Admin Signup</h1>

    <?php if (!empty($errors)): ?>
        <div class="message error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

        <label for="password">Password</label>
        <div class="input-group">
            <input type="password" id="password" name="password" required>
            <button type="button" class="toggle" onclick="togglePassword('password', this)">Show</button>
        </div>

        <label for="confirm_password">Confirm Password</label>
        <div class="input-group">
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="button" class="toggle" onclick="togglePassword('confirm_password', this)">Show</button>
        </div>

        <label for="admin_code">Admin Code</label>
        <div class="input-group">
            <input type="password" id="admin_code" name="admin_code" required>
            <button type="button" class="toggle" onclick="togglePassword('admin_code', this)">Show</button>
        </div>

        <button type="submit">Sign Up</button>
    </form>

    <a href="admin_selection.php">🏠︎ Back to Admin Selection</a>
</div>
</body>
</html>
