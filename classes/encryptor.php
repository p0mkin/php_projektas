<?php

class encryptor{
    private const CIPHER = "AES-256-CBC";
    public static function encrypt(string $plaintext, string $key): string{

        $ivlength = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivlength);

        $encrypted = openssl_encrypt($plaintext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv . $encrypted);
    }
    public static function decrypt(string $encrypted, string $key): string|false {

        $decoded = base64_decode($encrypted);
        [$iv, $encrypted] = explode("\0", $decoded);
        return openssl_decrypt($encrypted, self::CIPHER, $key, 0, $iv);
    }
    public static function generateKey(): string{
        return bin2hex(random_bytes(32));
    }
}