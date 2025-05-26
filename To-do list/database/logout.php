<?php
session_start();

require_once './connection.php'; 

// Check if session exists
if (isset($_SESSION['users']) && isset($_SESSION['users']['id'])) {
    // Create a new instance of the Connection class
    $conn = new Connection();
    $pdo = $conn->getConnection(); // Get the PDO connection

    // Get the user ID from the session
    $user_id = $_SESSION['users']['id'];

    try {
        // Update the user status to 'inactive'
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = :id");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        // Log error (optional)
        error_log("Logout Error: " . $e->getMessage());
    }

    // Remove all session variables and destroy session
    session_unset();
    session_destroy();
    session_write_close(); // Ensures data is saved before redirecting
}

// Redirect to the login page
header('Location: ../index.php');
exit();
?>
