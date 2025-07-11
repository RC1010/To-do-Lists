<?php
session_start();
header('Content-Type: application/json');
require_once('../database/database.php');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$category_id = $_POST['category_id'] ?? null;  // variable name fixed

// Validate category_id
if (!$category_id || !is_numeric($category_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid category ID']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->dbConnection();

    // Delete category without user_id check since no such column
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = :category_id");
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Category deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Category not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete category']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>