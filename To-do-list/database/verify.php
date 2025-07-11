<?php
require_once './database/connection.php';
$conn = new Connection();
$pdo = $conn->getConnection();

$token = $_GET['token'] ?? '';

if ($token) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = :token");
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();

    if ($user = $stmt->fetch()) {
        $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id");
        $update->bindParam(':id', $user['id'], PDO::PARAM_INT);
        $update->execute();

        echo "Email verified successfully. You can now log in.";
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}
?>
