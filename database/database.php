<?php

// Load configuration
require_once __DIR__ . '/../config/config.php';
Config::load();

class Database {
    private $host;
    private $username;
    private $password;
    private $dbname;

    private $conn = null;

    public function __construct() {
        // Load database credentials from environment variables
        $this->host = Config::get('DB_HOST', '127.0.0.1');
        $this->username = Config::get('DB_USERNAME', 'root');
        $this->password = Config::get('DB_PASSWORD', '');
        $this->dbname = Config::get('DB_NAME', 'Blotter_System');
    }

    public function connect() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // Log error without exposing credentials
                if (Config::isDebug()) {
                    throw $e;
                } else {
                    error_log("Database connection error: " . $e->getMessage());
                    throw new Exception("Database connection failed. Please contact administrator.");
                }
            }
        }
        return $this->conn;
    }
}