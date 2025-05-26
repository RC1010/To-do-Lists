<?php
ob_start(); // Prevent header issues
session_start();
require_once('./database/connection.php');

$conn = new Connection();
$db = $conn->getConnection();

// Check if the user is NOT logged in
if (!isset($_SESSION['users'])) {
    header('Location: index.php'); // Redirect to the login page
    exit();
}

// Fetch and display tasks from the database
$sql = "SELECT tasks.id, users.email, tasks.title, tasks.status, tasks.priority, 
        categories.name AS category_name, tasks.created_at
        FROM tasks
        JOIN users ON tasks.user_id = users.id
        JOIN categories ON tasks.category_id = categories.id
        ORDER BY tasks.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all categories from the database
$stmt = $db->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle task addition (with duplicate title check)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_title'], $_POST['category_id'])) {
    $title = trim($_POST['task_title']);
    $priority = trim($_POST['task_priority'] ?? 'Normal');
    $category_id = intval($_POST['category_id']);
    $user_id = $_SESSION['users']['id'];

    // Check for empty required fields
    if (!$title || !$category_id) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Task title and category are required.'];
        header("Location: categories.php");
        exit();
    }

    // Check if the task title already exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE title = ?");
    $stmt->execute([$title]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['response'] = [
            'success' => false,
            'message' => 'Task title already exists. Please use a different title.'
        ];
    } else {
        // Insert new task
        try {
            $stmt = $db->prepare("INSERT INTO tasks (user_id, title, priority, category_id, created_at) 
                                  VALUES (:user_id, :title, :priority, :category_id, NOW())");

            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':priority', $priority, PDO::PARAM_STR);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['response'] = ['success' => true, 'message' => 'Task added successfully!'];
            } else {
                $_SESSION['response'] = ['success' => false, 'message' => 'Error adding task.'];
            }
        } catch (PDOException $e) {
            $_SESSION['response'] = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    header("Location: categories.php");
    exit();
}

// Handle category addition (with duplicate category check)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);
    $category_description = trim($_POST['category_description'] ?? '');

    if (!$category_name) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Category name is required.'];
        header("Location: categories.php");
        exit();
    }

    try {
        // Check for duplicate category name
        $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE name = :name");
        $stmt->bindParam(':name', $category_name, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['response'] = ['success' => false, 'message' => 'Category already exists.'];
        } else {
            // Insert new category
            $stmt = $db->prepare("INSERT INTO categories (name, description, created_at) VALUES (:name, :description, NOW())");
            $stmt->bindParam(':name', $category_name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $category_description, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION['response'] = ['success' => true, 'message' => 'Category added successfully!'];
            } else {
                $_SESSION['response'] = ['success' => false, 'message' => 'Error adding category.'];
            }
        }
    } catch (PDOException $e) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }

    header("Location: categories.php");
    exit();
}

// Handle add priority (only update tasks with no existing priority)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['task_priority'])) {
    $task_id = intval($_POST['task_id']);
    $new_priority = trim($_POST['task_priority']);

    try {
        // Check if the task already has a priority
        $stmt = $db->prepare("SELECT priority FROM tasks WHERE id = :task_id");
        $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
        $stmt->execute();
        $current_priority = $stmt->fetchColumn();

        if (!empty($current_priority)) {
            $_SESSION['response'] = ['success' => false, 'message' => 'This task already has a priority.'];
        } else {
            // Update the priority since it's not set
            $stmt = $db->prepare("UPDATE tasks SET priority = :priority WHERE id = :task_id");
            $stmt->bindParam(':priority', $new_priority, PDO::PARAM_STR);
            $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['response'] = ['success' => true, 'message' => 'Priority added to task.'];
        }
    } catch (PDOException $e) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }

    header("Location: categories.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<?php include 'header/head.php'; ?>
<link rel="stylesheet" href="./css/categories.css">
    
<body class="bg-light">

<div id="content">
    <?php include 'header/nav.php'; ?>

    <div class="container-fluid d-flex justify-content-center pt-5">
        <?php if (isset($_SESSION['response'])): ?>
            <div id="alert-box" class="mx-auto alert alert-<?php echo $_SESSION['response']['success'] ? 'success' : 'danger'; ?>" role="alert">
                <?php echo $_SESSION['response']['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['response']); // Remove message after displaying ?>
        <?php endif; ?>
    </div>
    
    <main>
        <div class="main-container" id="main-container">
            <div class="container" id="container">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <h4><strong>Task Categories</strong></h4>
                    <!-- Add Category Button -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#categoryModal">
                        Add Category
                    </button>
                 </div>

                <!-- Modal -->
                <div class="modal fade pt-5" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                            </div>
                            <!-- Modal Body -->
                            <div class="modal-body">
                                <form action="" method="POST"> <!-- Form now submits to same page -->
                                    <!-- Category Name -->
                                    <div class="mb-3">
                                        <label for="category_name" class="form-label"><strong>Category Name</strong></label>
                                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                                    </div>

                                    <!-- Category Description -->
                                    <div class="mb-3">
                                        <label for="category_description" class="form-label"><strong>Description</strong></label>
                                        <textarea class="form-control" id="category_description" name="category_description" placeholder="Enter category description" rows="3"></textarea>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Category</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container" id="container3">
                        <table class="table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th class="p-2">#</th>
                                    <th class="p-2">Category Name</th>
                                    <th class="p-2">Description</th>
                                    <th class="p-2">Action</th>
                                </tr>
                            </thead>
                        <tbody>
                            <?php
                            if (!empty($categories)) {
                                $count = 1;
                                foreach ($categories as $category) {
                                    echo 
                                        "<tr>
                                            <td class='p-2'>{$count}</td>
                                            <td class='p-2'>{$category['name']}</td> <!-- Category Name -->
                                            <td class='p-2'>{$category['description']}</td> <!-- Category Description -->
                                            <td class='p-2'>
                                                <a href='edit-category.php?id={$category['id']}' class='btn btn-success btn-sm'>
                                                    <i class='bi bi-pencil'></i>
                                                </a>
                                                <a href='delete-category.php?id={$category['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this category?\")'>
                                                    <i class='bi bi-trash'></i>
                                                </a>
                                            </td>
                                        </tr>";
                                    $count++;
                                }
                            } else {
                                echo "<tr><td colspan='4' class='p-2 text-center'>No categories available</td></tr>";
                            }
                            ?>
                        </tbody>
                        </table>
                    </div>
            </div>

            <div class="container" id="container2">
                <div class="task-status">
                    <div class="container-fluid d-flex justify-content-between align-items-center">
                        <h4><strong>Task Status</strong></h4>
                        <!-- Add Task Button -->
                        <div id="btn">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#taskModal">
                                Add Task Status
                            </button>
                        </div>
                    </div>

                <!-- Task Modal -->
                <div class="modal fade pt-5" id="taskModal" tabindex="-1" role="dialog" aria-labelledby="taskModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="taskModalLabel">Add Task</h5>
                            </div>
                            <!-- Modal Body -->
                            <div class="modal-body">
                                <form action="" method="POST">
                                    <!-- Task Name -->
                                    <div class="mb-3">
                                        <label for="task_title" class="form-label"><strong>Title</strong></label>
                                        <input type="text" class="form-control" id="task_title" name="task_title" required>
                                    </div>

                                    <!-- Task Category -->
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label"><strong>Select Category</strong></label>
                                        <select class="form-control" id="category_id" name="category_id" required>
                                            <option value="">Select a Category</option>
                                            <?php
                                            // Fetch categories from the database
                                            $categoryStmt = $db->query("SELECT id, name FROM categories");
                                            while ($category = $categoryStmt->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='{$category['id']}'>{$category['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Task</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="container" id="container3">
                        <table class="table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th class="p-2">#</th>
                                    <th class="p-2">Title</th>
                                    <th class="p-2">Category</th>
                                    <th class="p-2">Task Status</th>
                                    <th class="p-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if (!empty($tasks)) {
                                $count = 1;
                                foreach ($tasks as $task) {
                                    echo "<tr>
                                            <td class='p-2'>{$count}</td>
                                            <td class='p-2'>{$task['title']}</td>
                                            <td class='p-2'>{$task['category_name']}</td>
                                            <td class='p-2'>{$task['status']}</td>
                                            <td>
                                                <a href='edit-task.php?id={$task['id']}' class='btn btn-success btn-sm'>
                                                    <i class='bi bi-pencil'></i>
                                                </a>
                                                <a href='delete-task.php?id={$task['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this status?\")'>
                                                    <i class='bi bi-trash'></i> 
                                                </a>
                                            </td>
                                        </tr>";
                                    $count++;
                                }
                            } else {
                                echo "<tr><td colspan='5' class='p-2 text-center'>No tasks available</td></tr>";
                            }
                            ?>
                        </tbody>
                        </table>
                    </div>
                    <hr>

                    <!-- Task Priority -->
                    <div class="container-fluid d-flex justify-content-between align-items-center">
                        <h4><strong>Task Priority</strong></h4>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#priorityModal">
                            <i class="bi bi-plus"></i>
                            Add New Priority
                        </button>
                    </div>

                    <!-- Add New Priority Modal -->
                    <div class="modal fade" id="priorityModal" tabindex="-1" role="dialog" aria-labelledby="priorityModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="priorityModalLabel">Add Task Priority</h5>
                                </div>
                                <div class="modal-body">
                                    <form action="" method="POST">
                                        <!-- Select Task -->
                                        <div class="mb-3">
                                            <label for="task_id" class="form-label"><strong>Select Task</strong></label>
                                            <select class="form-control" id="task_id" name="task_id" required>
                                                <option value="">Select a Task</option>
                                                <?php
                                                // Fetch tasks from the database
                                                $taskStmt = $db->query("SELECT id, title FROM tasks");
                                                while ($task = $taskStmt->fetch(PDO::FETCH_ASSOC)) {
                                                    echo "<option value='{$task['id']}'>{$task['title']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Select Priority Level -->
                                        <div class="mb-3">
                                            <label for="task_priority" class="form-label"><strong>Priority Level</strong></label>
                                            <select class="form-control" id="task_priority" name="task_priority" required>
                                                <option value="Low">Low</option>
                                                <option value="Medium">Medium</option>
                                                <option value="High">High</option>
                                            </select>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save Priority</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container" id="container3">
                        <table class="table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th class="p-2">#</th>
                                    <th class="p-2">Title</th>
                                    <th class="p-2">Category</th>
                                    <th class="p-2">Task Priority</th>
                                    <th class="p-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if (!empty($tasks)) {
                                $count = 1;
                                foreach ($tasks as $task) {
                                    echo "<tr>
                                            <td class='p-2'>{$count}</td>
                                            <td class='p-2'>{$task['title']}</td>
                                            <td class='p-2'>{$task['category_name']}</td>
                                            <td class='p-2'>{$task['priority']}</td>
                                            <td>
                                                <a href='edit-task.php?id={$task['id']}' class='btn btn-success btn-sm'>
                                                    <i class='bi bi-pencil'></i> 
                                                </a>
                                                <a href='delete-task.php?id={$task['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this priority?\")'>
                                                    <i class='bi bi-trash'></i>
                                                </a>
                                            </td>
                                        </tr>";
                                    $count++;
                                }
                            } else {
                                echo "<tr><td colspan='5' class='p-2 text-center'>No tasks available</td></tr>";
                            }
                            ?>
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>

<!-- Bootstrap JS (Required for Modal) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    setTimeout(function() {
        let alertBox = document.getElementById('alert-box');
        if (alertBox) {
            alertBox.classList.remove('show');
        }
    }, 4000);
</script>
