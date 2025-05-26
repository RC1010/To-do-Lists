<?php
require_once('database.php'); // Correct path if both files are in the same folder

class Connection {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->dbConnection(); // Get the PDO connection object
    }

    public function getConnection() {
        return $this->conn; // Provide a method to access the PDO connection
    }

    public function getAllUsers() {
        try {
            $query = "SELECT * FROM users";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return [];
        }
    }

    public function loginUser($email, $password) {
        try {
            if (empty($email) || empty($password)) {
                return ['error' => 'Email and password are required.'];
            }
    
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    return $user; // Success: return user data
                } else {
                    return ['error' => 'Incorrect password.'];
                }
            } else {
                return ['error' => 'No user found with this email.'];
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return ['error' => 'Something went wrong. Please try again.'];
        }
    }
}    
?>
