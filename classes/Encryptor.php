<?php
class Encryptor{
    private const CIPHER = "AES-256-CBC";
    public static function encrypt(string $plaintext, string $key): string{

        $ivLength = openssl_cipher_iv_length(self::CIPHER);

        if ($ivLength === false) {
            throw new RuntimeException('Invalid cipher.');
        }

        $iv = openssl_random_pseudo_bytes($ivLength);

        $encrypted = openssl_encrypt($plaintext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new RuntimeException('Encryption failed.');
        }

        return base64_encode($iv . $encrypted);
    }

    public static function decrypt(string $encrypted, string $key): string|false{

        $decoded = base64_decode($encrypted, true);

        if ($decoded === false) {
            return false;
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);

        if ($ivLength === false || strlen($decoded) <= $ivLength) {
            return false;
        }

        $iv = substr($decoded, 0, $ivLength);
        $ciphertext = substr($decoded, $ivLength);

        return openssl_decrypt($ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
    }

    public static function generateKey(): string{
        return bin2hex(random_bytes(32));
    }
}