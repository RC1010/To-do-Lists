<?php
session_start();
require_once('../database/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = intval($_POST['task_id']);
    $task_title = trim($_POST['task_title']);
    $task_description = trim($_POST['task_description']);
    $task_status = $_POST['task_status'];
    $task_priority = $_POST['task_priority'];
    $task_category_id = intval($_POST['task_category_id']);
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;

    // Validate status and priority
    $allowedStatuses = ['pending', 'in_progress', 'completed']; 
    $allowedPriorities = ['low', 'medium', 'high'];

    if (!in_array($task_status, $allowedStatuses)) {
        $task_status = 'pending';
    }
    if (!in_array($task_priority, $allowedPriorities)) {
        $task_priority = 'medium';
    }

    $database = new Database();
    $db = $database->dbConnection();

    if (empty($task_title)) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Task title is required.'];
        header("Location: ../task.php?page=$page#tasks");
        exit();
    }

    try {
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE title = :title AND category_id = :category_id AND id != :id");
        $checkStmt->bindValue(':title', $task_title);
        $checkStmt->bindValue(':category_id', $task_category_id);
        $checkStmt->bindValue(':id', $task_id);
        $checkStmt->execute();
        $existingCount = $checkStmt->fetchColumn();

        if ($existingCount > 0) {
            $_SESSION['response'] = ['success' => false, 'message' => 'A task with the same title already exists in this category.'];
            header("Location: ../task.php?page=$page#tasks");
            exit();
        }

        $stmt = $db->prepare("UPDATE tasks 
                              SET title = :title, description = :description, status = :status, priority = :priority, category_id = :category_id, updated_at = NOW()
                              WHERE id = :id");

        $stmt->bindValue(':title', $task_title);
        $stmt->bindValue(':description', $task_description);
        $stmt->bindValue(':status', $task_status);
        $stmt->bindValue(':priority', $task_priority);
        $stmt->bindValue(':category_id', $task_category_id);
        $stmt->bindValue(':id', $task_id);

        if ($stmt->execute()) {
            $_SESSION['response'] = ['success' => true, 'message' => 'Task updated successfully!'];
        } else {
            $_SESSION['response'] = ['success' => false, 'message' => 'Failed to update task.'];
        }

    } catch (PDOException $e) {
        error_log('Database error in edit-task.php: ' . $e->getMessage());
        $_SESSION['response'] = ['success' => false, 'message' => 'An internal error occurred. Please try again later.'];
    }

    header("Location: ../task.php?page=$page#tasks");
    exit();
}
?>
