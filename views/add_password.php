<?php
session_start();
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/PasswordGenerator.php';
require_once __DIR__ . '/../classes/PasswordEntry.php';

User::requireLogin();

$generatedPassword = '';
$message           = '';

if (isset($_POST['action']) && $_POST['action'] === 'generate') {
    $gen = new PasswordGenerator(
        (int)($_POST['lowercase'] ?? 2),
        (int)($_POST['uppercase'] ?? 2),
        (int)($_POST['numbers']   ?? 2),
        (int)($_POST['specials']  ?? 2)
    );
    $generatedPassword = $gen->generate();
}
if (isset($_POST['action']) && $_POST['action'] === 'save') {
    $title         = trim($_POST['title']    ?? '');
    $plainPassword = trim($_POST['password'] ?? '');

    if (empty($title) || empty($plainPassword)) {
        $message = 'Title and password are required.';
    } else {
        $entry  = new PasswordEntry();
        $saved  = $entry->save(
            $_SESSION['user_id'],
            $title,
            $plainPassword,
            $_SESSION['user_key']
        );
        if ($saved) {
            header('Location: dashboard.php?saved=1');
            exit;
        }
        $message = 'Something went wrong. Try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Add Password</title>
</head>
<body>
    <h1>Add password</h1>
    <a href="dashboard.php">← Back</a>

    <?php if ($message): ?>
        <p style="color:red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <!-- GENERATOR SECTION -->
    <h2>Generate a password</h2>
    <form method="POST" action="add_password.php">
        <input type="hidden" name="action" value="generate">

        <label>Lowercase letters: <input type="number" name="lowercase" value="2" min="0" max="20"></label><br>
        <label>Uppercase letters: <input type="number" name="uppercase" value="2" min="0" max="20"></label><br>
        <label>Numbers:           <input type="number" name="numbers"   value="2" min="0" max="20"></label><br>
        <label>Special symbols:   <input type="number" name="specials"  value="2" min="0" max="20"></label><br>

        <button type="submit">Generate</button>
    </form>

    <?php if ($generatedPassword): ?>
        <p>Generated: <strong><?= htmlspecialchars($generatedPassword) ?></strong></p>
    <?php endif; ?>

    <!-- SAVE SECTION -->
    <h2>Save a password</h2>
    <form method="POST" action="add_password.php">
        <input type="hidden" name="action" value="save">

        <label>Site / App name:
            <input type="text" name="title" required>
        </label><br>

        <label>Password:
            <input type="text" name="password"
                   value="<?= htmlspecialchars($generatedPassword) ?>">
        </label><br>

        <button type="submit">Save</button>
    </form>
</body>
</html>