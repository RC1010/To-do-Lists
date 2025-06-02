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

$task_id = $_POST['tasks_id'] ?? null;
$user_id = $_SESSION['user_id'];

// Validate task_id
if (!$task_id || !is_numeric($task_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid task ID']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->dbConnection();

    // Delete task only if it belongs to logged in user
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :task_id AND user_id = :user_id");
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Task deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Task not found or unauthorized']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete task']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>