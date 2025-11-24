<?php

namespace App\Config;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // MySQL configuration - supports environment variables for Docker
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'quickshop';
            $username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
            $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

            try {
                $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
                self::$instance = new PDO($dsn, $username, $password);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                // If database doesn't exist, provide helpful error
                if ($e->getCode() == 1049) {
                    throw new \RuntimeException(
                        "Database '{$dbname}' does not exist. Please run: php scripts/setup_database.php"
                    );
                }
                throw $e;
            }
        }

        return self::$instance;
    }
}