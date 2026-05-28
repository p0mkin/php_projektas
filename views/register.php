<?php
session_start();
require_once __DIR__ . '/../classes/User.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user   = new User();
    $result = $user->register(
        trim($_POST['username'] ?? ''),
        $_POST['password'] ?? ''
    );
    $message = $result['message'];
    if ($result['success']) {
        header('Location: login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Register — PassManager</title>
</head>
<body>
<h1>Create account</h1>

<?php if ($message): ?>
    <p style="color: red;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST" action="register.php">
    <label>Username:
        <input type="text" name="username" required>
    </label><br>

    <label>Password:
        <input type="password" name="password" required>
    </label><br>

    <button type="submit">Register</button>
</form>

<p>Already have an account? <a href="login.php">Log in</a></p>
</body>
</html>