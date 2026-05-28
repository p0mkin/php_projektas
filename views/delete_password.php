<?php

session_start();
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/PasswordEntry.php';

User::requireLogin();

$id = (int)($_POST['id'] ?? 0);  // cast to int — kills any SQL injection attempt

if ($id > 0) {
    $entry = new PasswordEntry();
    $entry->delete($id, $_SESSION['user_id']);
}

header('Location: dashboard.php');
exit;