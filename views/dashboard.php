<?php
session_start();
require_once __DIR__ . '/../classes/User.php';

User::requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — PassManager</title>
</head>
<body>
<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
<p>You are logged in. </p>

<nav>
    <a href="add_password.php">Add password</a> |
    <a href="logout.php">Log out</a>
</nav>
</body>
</html>