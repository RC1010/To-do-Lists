<?php
session_start();
require_once('../database/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id']);
    $category_name = trim($_POST['category_name']);
    $category_description = trim($_POST['category_description']);

    // Connect to database
    $database = new Database();
    $db = $database->dbConnection(); // Fix: use $db not $conn

    if (empty($category_name)) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Category name is required.'];
        header("Location: edit-category.php?id=$category_id");
        exit();
    }

    try {
        // Check if the category name already exists (excluding current category)
        $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE name = :name AND id != :id");
        $stmt->bindParam(':name', $category_name);
        $stmt->bindParam(':id', $category_id);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['response'] = ['success' => false, 'message' => 'Category name already exists.'];
        } else {
            // Update the category
            $stmt = $db->prepare("UPDATE categories SET name = :name, description = :description, updated_at = NOW() WHERE id = :id");
            $stmt->bindParam(':name', $category_name);
            $stmt->bindParam(':description', $category_description);
            $stmt->bindParam(':id', $category_id);

            if ($stmt->execute()) {
                $_SESSION['response'] = ['success' => true, 'message' => 'Category updated successfully!'];
            } else {
                $_SESSION['response'] = ['success' => false, 'message' => 'Error updating category.'];
            }
        }
    } catch (PDOException $e) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }

    header("Location: ../task.php#category"); // Redirect back to category section in task.php
    exit();
}
?>
