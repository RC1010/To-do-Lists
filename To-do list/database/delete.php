<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'connection.php'; 

    $id = $_POST['id'] ?? null;

    if ($id) {
        // Prepare and execute the deletion
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Task deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete task']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID not provided.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
