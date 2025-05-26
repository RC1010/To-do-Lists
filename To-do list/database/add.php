<?php
session_start();
require_once('./connection.php'); // Adjust the path

$conn = new Connection();
$db = $conn->getConnection();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php'); 
    exit();
}

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type'])) {
    $type = $_POST['type'];

    try {
        if ($type === 'category') {
            // Add Category
            $category_name = trim($_POST['category_name']) ?? null;
            $category_description = trim($_POST['category_description']) ?? '';

            if (!$category_name) {
                die("Error: Category name is required.");
            }

            $stmt = $db->prepare("INSERT INTO categories (name, description, created_at) VALUES (:name, :description, NOW())");
            $stmt->bindParam(':name', $category_name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $category_description, PDO::PARAM_STR);
            $stmt->execute();

            header("Location: ../categories.php?success=Category added!");
            exit();

        } elseif ($type === 'task') {
            // Add Task
            $title = trim($_POST['title']) ?? null;
            $description = trim($_POST['description']) ?? '';
            $status = $_POST['status'] ?? 'pending';
            $priority = $_POST['priority'] ?? 'normal';
            $user_id = $_SESSION['user_id'];

            if (!$title) {
                die("Error: Task title is required.");
            }

            $stmt = $db->prepare("INSERT INTO tasks (user_id, title, description, status, priority, created_at) 
                                  VALUES (:user_id, :title, :description, :status, :priority, NOW())");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':priority', $priority, PDO::PARAM_STR);
            $stmt->execute();

            header("Location: ../tasks.php?success=Task added!");
            exit();

        } else {
            die("Invalid type.");
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    die("Invalid request.");
}
?>
