<?php

session_start();
require_once __DIR__ . '/../classes/User.php';

User::requireLogin();

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user   = new User();
    $result = $user->changePassword(
        $_POST['old_password'] ?? '',
        $_POST['new_password'] ?? ''
    );
    $message = $result['message'];
    $success = $result['success'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Change Password</title>
</head>
<body>
    <h1>Change password</h1>
    <a href="dashboard.php">← Back</a>

    <?php if ($message): ?>
        <p style="color: <?= $success ? 'green' : 'red' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="change_password.php">
        <label>Current password:
            <input type="password" name="old_password" required>
        </label><br>

        <label>New password:
            <input type="password" name="new_password" required minlength="6">
        </label><br>

        <button type="submit">Change password</button>
    </form>
</body>
</html>