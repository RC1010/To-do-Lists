<?php
session_start();
require_once('./connection.php');

$conn = new Connection();
$db = $conn->getConnection();

if (!isset($_SESSION['users'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['users']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type'])) {
    $type = $_POST['type'];

    try {
        if ($type === 'category') {
            $category_name = trim($_POST['category_name']) ?? null;
            $category_description = trim($_POST['category_description']) ?? '';

            if (!$category_name) {
                $_SESSION['response'] = ['success' => false, 'message' => 'Error: Category name is required.'];
                header("Location: ../task.php#category");
                exit();
            }

            $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE name = :name AND user_id = :user_id");
            $stmt->bindParam(':name', $category_name);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                $_SESSION['response'] = ['success' => false, 'message' => 'Error: Category already exists.'];
                header("Location: ../task.php#category");
                exit();
            }

            $stmt = $db->prepare("INSERT INTO categories (user_id, name, description, created_at) VALUES (:user_id, :name, :description, NOW())");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $category_name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $category_description, PDO::PARAM_STR);
            $stmt->execute();

            $_SESSION['response'] = ['success' => true, 'message' => 'Category added successfully!'];
            header("Location: ../task.php#category");
            exit();

        } elseif ($type === 'task') {
            $title = trim($_POST['task_title']);
            $priority = trim($_POST['task_priority'] ?? 'Normal');
            $status = trim($_POST['task_status'] ?? 'Not Started');
            $description = isset($_POST['task_description']) ? trim($_POST['task_description']) : '';
            $due_date = isset($_POST['due_date']) ? $_POST['due_date'] : null;
            $category_id = intval($_POST['category_id']);

            if (empty($title) || empty($category_id) || empty($description)) {
                $_SESSION['response'] = ['success' => false, 'message' => 'Error: All fields are required.'];
                header("Location: ../task.php");
                exit();
            }

            $stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE title = ? AND user_id = ? AND category_id = ?");
            $stmt->execute([$title, $user_id, $category_id]);

            if ($stmt->fetchColumn() > 0) {
                $_SESSION['response'] = ['success' => false, 'message' => 'Error: Task title already exists.'];
                header("Location: ../task.php");
                exit();
            }

            $stmt = $db->prepare("INSERT INTO tasks (user_id, title, description, priority, category_id, status, due_date, created_at)
                                  VALUES (:user_id, :title, :description, :priority, :category_id, :status, :due_date, NOW())");

            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':priority', $priority);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':due_date', $due_date);

            if ($stmt->execute()) {
                $_SESSION['response'] = ['success' => true, 'message' => 'Task added successfully!'];
            } else {
                $_SESSION['response'] = ['success' => false, 'message' => 'Error adding task.'];
            }

            header("Location: ../task.php");
            exit();

        } else {
            $_SESSION['response'] = ['success' => false, 'message' => 'Invalid form type.'];
            header("Location: ../task.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        header("Location: ../task.php");
        exit();
    }
} else {
    $_SESSION['response'] = ['success' => false, 'message' => 'Invalid request.'];
    header("Location: ../task.php");
    exit();
}

?>
