<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Encryptor.php';

class User{
    private Database $db;

    private ?int $id = null;
    private ?string $username = null;
    private ?string $encryptedKey = null;

    public function __construct(Database $db){
        $this->db = Database::getInstance();
    }

    public function Register(string $username, string $plainPassword):array{

        if (empty($username) || empty($plainPassword)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }
        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Username must be at least 3 characters.'];
        }
        if (strlen($plainPassword) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }
        $existing = $this->db->query(
            "SELECT id FROM users WHERE username = ?",
            [$username]
        );
        if (!empty($existing)) {
            return ['success' => false, 'message' => 'Username already taken.'];
        }
        $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $plainKey = Encryptor::generateKey();
        $encryptedKey = Encryptor::encrypt($plainKey, $plainPassword);

        $this->db->execute(
            "INSERT INTO users (username, password_hash, encrypted_key) VALUES (?, ?, ?)",
            [$username, $passwordHash, $encryptedKey]
        );

        return ['success' => true, 'message' => 'Account created! Please log in.'];
    }

    public function login(string $username, string $plainPassword): array {
        $rows = $this->db->query(
            "SELECT id, username, password_hash, encrypted_key FROM users WHERE username = ?",
            [$username]
        );

        if (empty($rows)) {// Don't say "username not found" — that leaks info to attackers
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }
        $user = $rows[0];
        // 2. Verify the password against the stored hash
        if (!password_verify($plainPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        $plainKey = Encryptor::decrypt($user['encrypted_key'], $plainPassword);

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_key'] = $plainKey;

        return ['success' => true, 'message' => 'Welcome back!'];
    }
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: /passmanager/views/login.php');
            exit;
        }
    }
    public static function logout(): void {
        session_destroy();
        header('Location: /passmanager/views/login.php');
        exit;
    }

}