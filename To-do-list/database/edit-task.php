<?php
session_start();
require_once('./database.php');

$database = new Database();
$db = $database->dbConnection();


// Validate and Update Task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['task_title'], $_POST['task_description'])) {
    $task_id = intval($_POST['task_id']);
    $task_title = trim($_POST['task_title']);
    $task_description = trim($_POST['task_description']);
    $task_status = trim($_POST['task_status']);
    $due_date = trim($_POST['due_date']);
    $task_priority = trim($_POST['task_priority']);
    $task_category_id = intval($_POST['task_category_id']);
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $user_id = $_SESSION['users']['id'];

    // Validate allowed values
    $allowedStatuses = ['Not Started', 'Pending', 'In Progress', 'Completed'];
    $allowedPriorities = ['low', 'medium', 'high'];

    if (!in_array($task_status, $allowedStatuses)) {
        $task_status = 'Pending';
    }
    if (!in_array($task_priority, $allowedPriorities)) {
        $task_priority = 'medium';
    }

    if (!$task_title || !$task_description || !$task_priority || !$task_status) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Please fill in all required fields.'];
        header("Location: ../task.php#tasks");
        exit();
    }

    try {
        // Check for duplicate title
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE title = :title AND category_id = :category_id AND id != :id AND user_id = :user_id");
        $checkStmt->bindValue(':title', $task_title);
        $checkStmt->bindValue(':category_id', $task_category_id);
        $checkStmt->bindValue(':id', $task_id);
        $checkStmt->bindValue(':user_id', $user_id);
        $checkStmt->execute();

        if ($checkStmt->fetchColumn() > 0) {
            $_SESSION['response'] = ['success' => false, 'message' => 'A task with the same title already exists in this category.'];
            header("Location: ../task.php#tasks");
            exit();
        }

        // Update task
        $stmt = $db->prepare("UPDATE tasks SET title = :title, description = :description, status = :status, due_date = :due_date,
                              priority = :priority, category_id = :category_id, updated_at = NOW()
                              WHERE id = :id AND user_id = :user_id");

        $stmt->bindParam(':title', $task_title);
        $stmt->bindParam(':description', $task_description);
        $stmt->bindParam(':status', $task_status);
        $stmt->bindParam(':due_date', $due_date);
        $stmt->bindParam(':priority', $task_priority);
        $stmt->bindParam(':category_id', $task_category_id);
        $stmt->bindParam(':id', $task_id);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            $_SESSION['response'] = ['success' => true, 'message' => 'Task updated successfully!'];
        } else {
            $_SESSION['response'] = ['success' => false, 'message' => 'Failed to update task.'];
        }

    } catch (PDOException $e) {
        error_log('Database error in edit-task.php: ' . $e->getMessage());
        $_SESSION['response'] = ['success' => false, 'message' => 'An internal error occurred. Please try again later.'];
    }

    header("Location: ../task.php#tasks");
    exit();
}
?>
