<?php

require_once __DIR__ . '/../config.php';

class Database { // Database Singleton
    private PDO $pdo;
    private static ?Database $instance = null;
    private function __construct()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                     PDO::ATTR_EMULATE_PREPARES => false
                   ];
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    public static function getInstance(): Database {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    public function query(string $query, array $params = []): array {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function execute(string $query, array $params = []): bool {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }
    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }
}