<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Encryptor.php';
class PasswordEntry
{
    private Database $db;
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    public function save(int $userId, string $title, string $plainPassword, string $userKey): bool
    {
        $encrypted = Encryptor::encrypt($plainPassword, $userKey);
        return $this->db->execute(
            "INSERT INTO passwords (user_id, title, encrypted_password) VALUES (?, ?, ?)",
            [$userId, $title, $encrypted]
        );
    }
    public function getAllForUser(int $userId, string $userKey): array
    {
        $rows = $this->db->query(
            "SELECT id, title, encrypted_password, created_at
             FROM passwords
             WHERE user_id = ?
             ORDER BY created_at DESC",
            [$userId]
        );
        foreach ($rows as &$row) {
            $row['password'] = Encryptor::decrypt($row['encrypted_password'], $userKey);
        }

        return $rows;
    }
    public function delete(int $entryId, int $userId): bool
    {
        return $this->db->execute(
            "DELETE FROM passwords WHERE id = ? AND user_id = ?",
            [$entryId, $userId]
        );
    }
}
