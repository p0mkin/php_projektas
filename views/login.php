<?php
session_start();

require_once __DIR__ . '/../classes/User.php';

// If already logged in, no need to see the login page
if (User::isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$message = '';

if (isset($_GET['registered'])) {
    $message = 'Account created! Please log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user   = new User();
    $result = $user->login(
        trim($_POST['username'] ?? ''),
        $_POST['password'] ?? ''
    );
    $message = $result['message'];

    if ($result['success']) {
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login — PassManager</title>
</head>
<body>
<h1>Log in</h1>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST" action="login.php">
    <label>Username:
        <input type="text" name="username" required>
    </label><br>

    <label>Password:
        <input type="password" name="password" required>
    </label><br>

    <button type="submit">Log in</button>
</form>

<p>No account? <a href="register.php">Register</a></p>
</body>
</html>