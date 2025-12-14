<?php
// classes/user.php

require_once __DIR__ . "/../database/database.php";

class User {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Register a new user
     */
    public function register($username, $password, $email = null) {
        // Check if username already exists
        $sql = "SELECT id FROM users WHERE username = :username";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $username);
        $query->execute();
        
        if ($query->fetch()) {
            return false; // Username already exists
        }

        // Check if email already exists (if provided)
        if (!empty($email)) {
            $sql = "SELECT id FROM users WHERE email = :email";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(":email", $email);
            $query->execute();
            
            if ($query->fetch()) {
                return 'email_exists'; // Email already exists
            }
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user (all users are regular users by default)
        $sql = "INSERT INTO users (username, password, email, created_at) VALUES (:username, :password, :email, NOW())";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $username);
        $query->bindParam(":password", $hashedPassword);
        $query->bindParam(":email", $email);
        
        return $query->execute();
    }

    /**
     * Login user
     */
    public function login($username, $password) {
        $sql = "SELECT id, username, password FROM users WHERE username = :username";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $username);
        $query->execute();
        
        $user = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
}
