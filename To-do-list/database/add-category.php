<?php
session_start();
require_once('connection.php');

$conn = new Connection();
$db = $conn->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);
    $category_description = trim($_POST['category_description'] ?? '');

    if (!$category_name) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Category name is required.'];
        header("Location: ../task.php#category");
        exit();
    }

    try {
        // Check if the category already exists for this user
        $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE name = :name AND user_id = :user_id");
        $stmt->bindParam(':name', $category_name);
        $stmt->bindParam(':user_id', $_SESSION['users']['id'], PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $_SESSION['response'] = ['success' => false, 'message' => 'Category already exists.'];
        } else {
            // Insert the new category
            $stmt = $db->prepare("INSERT INTO categories (name, description, user_id, created_at) VALUES (:name, :description, :user_id, NOW())");
            $stmt->bindParam(':name', $category_name);
            $stmt->bindParam(':description', $category_description);
            $stmt->bindParam(':user_id', $_SESSION['users']['id'], PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['response'] = ['success' => true, 'message' => 'Category added successfully!'];
        }
    } catch (PDOException $e) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }

    header("Location: ../task.php#category");
    exit();
} else {
    $_SESSION['response'] = ['success' => false, 'message' => 'Invalid request.'];
    header("Location: ../task.php#category");
    exit();
}
