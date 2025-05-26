<?php
ob_start();
session_start();
require_once('./database/connection.php');

$conn = new Connection();
$db = $conn->getConnection();

if (!isset($_SESSION['users'])) {
    header('Location: index.php');
    exit();
}

// Handle task addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_title'], $_POST['category_id'])) {
    $title = trim($_POST['task_title']);
    $priority = trim($_POST['task_priority'] ?? 'Normal');
    $description = isset($_POST['task_description']) ? trim($_POST['task_description']) : '';
    $category_id = intval($_POST['category_id']);
    $user_id = $_SESSION['users']['id'];

    if (empty($title) || empty($category_id) || empty($description)) {
        $_SESSION['response'] = ['success' => false, 'message' => 'All fields are required.'];
        header("Location: task.php");
        exit();
    }

    // Check for duplicates (per user)
    $stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE title = ? AND user_id = ?");
    $stmt->execute([$title, $user_id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Task title already exists.'];
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO tasks (user_id, title, description, priority, category_id, created_at)
                                  VALUES (:user_id, :title, :description, :priority, :category_id, NOW())");

            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':priority', $priority);
            $stmt->bindParam(':category_id', $category_id);

            if ($stmt->execute()) {
                $_SESSION['response'] = ['success' => true, 'message' => 'Task added successfully!'];
            } else {
                $_SESSION['response'] = ['success' => false, 'message' => 'Error adding task.'];
            }
        } catch (PDOException $e) {
            $_SESSION['response'] = ['success' => false, 'message' => 'DB error: ' . $e->getMessage()];
        }
    }

    header("Location: task.php");
    exit();
}

// Handle category addition (with duplicate category check)
$categoriesStmt = $db->query("SELECT id, name, description, created_at, updated_at FROM categories");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);
    $category_description = trim($_POST['category_description'] ?? '');

    if (!$category_name) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Category name is required.'];
        header("Location: task.php");
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

    header("Location: task.php");
    exit();
}

// Pagination setup task
$tasksPerPage = 5;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $tasksPerPage;
$user_id = $_SESSION['users']['id'];

// Get total task count for user
$totalStmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
$totalStmt->execute([$user_id]);
$totalTasks = $totalStmt->fetchColumn();
$totalPages = ceil($totalTasks / $tasksPerPage);

// Fetch tasks for the current page
$stmt = $db->prepare("SELECT t.*, c.name as category_name FROM tasks t
                      LEFT JOIN categories c ON t.category_id = c.id
                      WHERE t.user_id = :user_id
                      ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $tasksPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination for Categories
$categoriesPerPage = 5;
$categoryPage = isset($_GET['cat_page']) ? max(1, intval($_GET['cat_page'])) : 1;
$categoryOffset = ($categoryPage - 1) * $categoriesPerPage;

// Get total number of categories
$totalCategoryStmt = $db->query("SELECT COUNT(*) FROM categories");
$totalCategories = $totalCategoryStmt->fetchColumn();
$totalCategoryPages = ceil($totalCategories / $categoriesPerPage);

// Fetch paginated categories
$categoryStmt = $db->prepare("SELECT id, name, description, created_at, updated_at FROM categories ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$categoryStmt->bindValue(':limit', $categoriesPerPage, PDO::PARAM_INT);
$categoryStmt->bindValue(':offset', $categoryOffset, PDO::PARAM_INT);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<?php include 'header/head.php'; ?>
<link rel="stylesheet" href="./css/task.css">
    
<body class="bg-light">

<div id="content">
    <?php include 'header/nav.php'; ?>

    <main>
     <div class="container" id="container">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="task-tab" data-bs-toggle="tab" href="#task" role="tab">Create Task</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="tasks-tab" data-bs-toggle="tab" href="#tasks" role="tab">Tasks</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="category-tab" data-bs-toggle="tab" href="#category" role="tab">Category</a>
        </li>
    </ul>

    <div class="tab-content pt-3">
        <div class="tab-pane fade show active" id="task" role="tabpanel" aria-labelledby="task-tab">
            <div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h5 class="title">Add New Task</h5>
        </div>
        <div class="card-body">
            <form action="" method="POST"> <!-- Form STARTS here -->

                <!-- Category -->
                <div class="mb-3">
                    <label for="category_id" class="form-label"><strong>Select Category</strong></label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <option value="">Select a Category</option>
                        <?php
                        $categoryStmt = $db->query("SELECT id, name FROM categories");
                        while ($category = $categoryStmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$category['id']}'>{$category['name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Priority -->
                <div class="mb-3">
                    <label for="task_priority" class="form-label"><strong>Priority Level</strong></label>
                    <select class="form-control" id="task_priority" name="task_priority" required>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <!-- Title -->
                <div class="form-group mb-3">
                    <label for="task_title">Title</label>
                    <input type="text" id="task_title" name="task_title" class="form-control" required>
                </div>

                <!-- Description -->
                <div class="form-group mb-3">
                    <label for="task_description">Description</label>
                    <textarea id="task_description" name="task_description" class="form-control custom-textarea" required></textarea>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end">
                    <a href="your_previous_page.php" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Task</button>
                </div>
            </form> <!-- Form ENDS here -->
        </div>
    </div>
</div>
        </div>

        <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <h4><strong>Tasks</strong></h4>
            </div>

            <div class="container" id="container2">
                <table class="table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th class="p-2">#</th>
                            <th class="p-2">Title</th>
                            <th class="p-2">Description</th>
                            <th class="p-2">Created At</th>
                            <th class="p-2">Updated At</th>
                            <th class="p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (!empty($tasks)) {
                                $count = 1;
                                foreach ($tasks as $task):
                                    $desc = htmlspecialchars($task['description']);
                                    $words = explode(' ', $desc);
                                    $shortDesc = implode(' ', array_slice($words, 0, 3)) . (count($words) > 3 ? '...' : '');
                            ?>
                                <tr>
                                    <td class="p-2"><?= $count ?></td>
                                    <td class="p-2"><?= htmlspecialchars($task['title']) ?></td>
                                    <td class="p-2" title="<?= $desc ?>"><?= $shortDesc ?></td>
                                    <td class="p-2"><?= $task['created_at'] ?></td>
                                    <td class="p-2"><?= $task['updated_at'] ?></td>
                                    <td class="p-2">
                                        <a href="edit-task.php?id=<?= $task['id'] ?>" class="btn btn-success btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete-task.php?id=<?= $task['id'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this task?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php
                                    $count++;
                                endforeach;
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No tasks available</td></tr>";
                            }
                            ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Button -->
                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                       <a class="page-link" href="?page=<?= $currentPage - 1 ?>&cat_page=<?= $categoryPage ?>">Previous</a>
                        </li>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&cat_page=<?= $categoryPage ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>

                        <!-- Next Button -->
                         <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>">Next</a>
                        </li>
                    </ul>
                    </nav>
                <?php endif; ?>
            </div>

        <div class="tab-pane fade" id="category" role="tabpanel" aria-labelledby="category-tab">
                    
                    <!-- Header Section -->
                    <div class="container-fluid d-flex justify-content-between align-items-center my-3">
                        <h4><strong>Task Categories</strong></h4>

                        <!-- Add Category Button -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                            Add Category
                        </button>
                    </div>

                    <!-- Add Category Modal -->
                    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                
                                <form action="" method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <!-- Category Name -->
                                        <div class="mb-3">
                                            <label for="category_name" class="form-label"><strong>Category Name</strong></label>
                                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                                        </div>

                                        <!-- Category Description -->
                                        <div class="mb-3">
                                            <label for="category_description" class="form-label"><strong>Description</strong></label>
                                            <textarea class="form-control" id="category_description" name="category_description" rows="3" placeholder="Enter category description"></textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Category</button>
                                    </div>
                                </form>
                                
                            </div>
                        </div>
                    </div>

                    <!-- Category Table -->
                     <div class="container" id="container2">
                        <table class="table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th class="p-2">#</th>
                                    <th class="p-2">Category Name</th>
                                    <th class="p-2">Description</th>
                                    <th class="p-2">Created At</th>
                                    <th class="p-2">Updated At</th>
                                    <th class="p-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($categories)) {
                                    $count = 1;
                                    foreach ($categories as $category) {
                                        echo "
                                        <tr>
                                            <td class='p-2'>{$count}</td>
                                            <td class='p-2'>" . htmlspecialchars($category['name']) . "</td>
                                            <td class='p-2'>" . htmlspecialchars($category['description']) . "</td>
                                            <td class='p-2'>" . (isset($category['created_at']) ? htmlspecialchars($category['created_at']) : 'N/A') . "</td>
                                            <td class='p-2'>" . (isset($category['updated_at']) ? htmlspecialchars($category['updated_at']) : 'N/A') . "</td>

                                            <td class='p-2'>
                                                <a href='./database/edit-category.php?id=" . $category['id'] . "' class='btn btn-success btn-sm me-1'>
                                                    <i class='bi bi-pencil'></i>
                                                </a>
                                                <a href='delete-category.php?id={$category['id']}' class='btn btn-danger btn-sm' 
                                                onclick='return confirm(\"Are you sure you want to delete this category?\")'>
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
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <!-- Previous Button -->
                                    <li class="page-item <?= $categoryPage <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $categoryPage - 1 ?>">Previous</a>
                                    </li>

                                    <!-- Page Numbers -->
                                    <?php for ($i = 1; $i <= $totalCategoryPages; $i++): ?>
                                    <li class="page-item <?= $i == $categoryPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <li class="page-item <?= $categoryPage >= $totalCategoryPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $categoryPage + 1 ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


</body>
</html>
<!-- Bootstrap JS (Required for Modal) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 JS (with Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    setTimeout(function() {
        let alertBox = document.getElementById('alert-box');
        if (alertBox) {
            alertBox.classList.remove('show');
        }
    }, 4000);
</script> 
