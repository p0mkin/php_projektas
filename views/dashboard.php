<?php
session_start();
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/PasswordEntry.php';

User::requireLogin();

$entry     = new PasswordEntry();
$passwords = $entry->getAllForUser($_SESSION['user_id'], $_SESSION['user_key']);

$savedMsg  = isset($_GET['saved']) ? 'Password saved!' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
<a href="add_password.php">+ Add password</a> |
<a href="logout.php">Log out</a>

<?php if ($savedMsg): ?>
    <p style="color:green"><?= $savedMsg ?></p>
<?php endif; ?>

<h2>Your vault</h2>

<?php if (empty($passwords)): ?>
    <p>No passwords saved yet.</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>Site / App</th>
            <th>Password</th>
            <th>Saved at</th>
            <th>Action</th>
        </tr>
        <?php foreach ($passwords as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= htmlspecialchars($p['password']) ?></td>
                <td><?= htmlspecialchars($p['created_at']) ?></td>
                <td>
                    <form method="POST" action="delete_password.php">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit"
                                onclick="return confirm('Delete this entry?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
</body>
</html>