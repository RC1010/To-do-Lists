<?php
session_start();
require_once('connection.php');

// Ensure user is logged in using the new session variable
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$connObj = new Connection();
$db = $connObj->getConnection();

$userId = $_SESSION['user_id'];
$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$imageName = $_FILES['profile_image']['name'] ?? null;
$imageTmp = $_FILES['profile_image']['tmp_name'] ?? null;

try {
    if ($imageName) {
        // Handle profile image upload
        $targetDir = '../uploads/';
        $fileExtension = pathinfo($imageName, PATHINFO_EXTENSION);
        $newImageName = uniqid('profile_', true) . '.' . $fileExtension;
        $uploadPath = $targetDir . $newImageName;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (!move_uploaded_file($imageTmp, $uploadPath)) {
            throw new Exception("Failed to upload image.");
        }

        // Update with profile image
        $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, profile_image = :image WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':image', $newImageName);
    } else {
        // Update without profile image
        $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE id = :id";
        $stmt = $db->prepare($query);
    }

    $stmt->bindParam(':first_name', $firstName);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':id', $userId);

    if ($stmt->execute()) {
        // Refetch updated user data
        $userQuery = $db->prepare("SELECT * FROM users WHERE id = :id");
        $userQuery->bindParam(':id', $userId);
        $userQuery->execute();
        $updatedUser = $userQuery->fetch(PDO::FETCH_ASSOC);

        $_SESSION['user'] = $updatedUser; // ✅ Sync updated info into session

        $_SESSION['response'] = [
            'success' => true,
            'message' => 'Profile updated successfully.'
        ];
    } else {
        throw new Exception("Database update failed.");
    }
} catch (Exception $e) {
    $_SESSION['response'] = [
        'success' => false,
        'message' => "Error: " . $e->getMessage()
    ];
}

header("Location: ../profile.php");
exit;
