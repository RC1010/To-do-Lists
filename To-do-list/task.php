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


// Pagination setup task
$tasksPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $tasksPerPage;
$user_id = $_SESSION['users']['id'];

// Get total task count for user
$totalStmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
$totalStmt->execute([$user_id]);
$totalTasks = $totalStmt->fetchColumn(); // This gets the COUNT correctly
$totalPages = ceil($totalTasks / $tasksPerPage); // This calculates total pages

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
$categoriesPerPage = 10;
$categoryPage = isset($_GET['cat_page']) ? max(1, intval($_GET['cat_page'])) : 1;
$categoryOffset = ($categoryPage - 1) * $categoriesPerPage;

// Get total number of categories
$totalCategoryStmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE user_id = :user_id");
$totalCategoryStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$totalCategoryStmt->execute();
$totalCategories = $totalCategoryStmt->fetchColumn();
$totalCategoryPages = ceil($totalCategories / $categoriesPerPage);


// Fetch paginated categories
$categoryStmt = $db->prepare("SELECT id, name, description, created_at, updated_at FROM categories 
                              WHERE user_id = :user_id
                              ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$categoryStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$categoryStmt->bindValue(':limit', $categoriesPerPage, PDO::PARAM_INT);
$categoryStmt->bindValue(':offset', $categoryOffset, PDO::PARAM_INT);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC); // ✅ ADD THIS


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
     
    <!-- Alert -->
    <?php if (isset($_SESSION['response'])): ?>
        <div class="alert alert-<?= $_SESSION['response']['success'] ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['response']['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['response']); ?>
    <?php endif; ?>
    
    <!-- Tab -->
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

    <!-- Create Tasks -->
    <div class="tab-content pt-3">
        <div class="tab-pane fade show active" id="task" role="tabpanel" aria-labelledby="task-tab">
            <div class="container mt-4">
                <div class="card bg-transparent border-0">
                    <div class="card-header">
                        <h5 class="title">Add New Task</h5>
                    </div>
                    <div class="card-body">
                        <form action="./database/add.php" method="POST"> <!-- Form STARTS here -->
                            <input type="hidden" name="type" value="task">
                            <!-- Row container for side-by-side layout -->
                            <div class="row">
                                <!-- Category (left side) -->
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label"><strong>Select Category</strong></label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select a Category</option>
                                        <?php
                                        $categoryStmt = $db->prepare("SELECT id, name FROM categories WHERE user_id = :user_id");
                                        $categoryStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                                        $categoryStmt->execute();

                                        while ($category = $categoryStmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='{$category['id']}'>" . htmlspecialchars($category['name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Priority (right side) -->
                                <div class="col-md-6 mb-3">
                                    <label for="task_priority" class="form-label"><strong>Priority Level</strong></label>
                                    <select class="form-select" id="task_priority" name="task_priority" required>
                                        <option value="Low">Low</option>
                                        <option value="Medium">Medium</option>
                                        <option value="High">High</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <!-- Status -->
                                <div class="col-md-6 mb-3">
                                    <label for="task_status" class="form-label"><strong>Status</strong></label>
                                    <select class="form-select" id="task_status" name="task_status" required>
                                        <option value="Not Started">Not Started</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>

                                <!-- Due Date -->
                                <div class="col-md-6 mb-3">
                                <label for="due_date"><strong>Due Date:</strong></label>
                                <input type="date" name="due_date" id="due_date" class="form-control">
                                </div>
                            </div>

                            <!-- Title -->
                            <div class="form-group mb-3">
                                <label for="task_title"><strong>Title</strong></label>
                                <input type="text" id="task_title" name="task_title" class="form-control" required>
                            </div>

                            <!-- Description -->
                            <div class="form-group mb-3">
                                <label for="task_description"><strong>Description</strong></label>
                                <textarea id="task_description" name="task_description" class="form-control custom-textarea" required></textarea>
                            </div>

                            <!-- Buttons -->
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Save Task</button>
                            </div>
                        </form> <!-- Form ENDS here -->
                        </div>
                    </div>
                </div>
            </div>

        <!-- Task Lists -->
        <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
            <div class="container-fluid d-flex justify-content-between align-items-center" data-bs-toggle="tab" href="#task-list">
                <h4><strong>Tasks</strong></h4>
            </div>
            <div class="container" id="container2">
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-dark">
                        <tr>
                            <th class="p-2">#</th>
                            <th class="p-2">Title</th>
                            <th class="p-2">Description</th>
                            <th class="p-2">Category</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Due Date</th>
                            <th class="p-2">Created At</th>
                            <th class="p-2">Updated At</th>
                            <th class="p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (!empty($tasks)) {
                                $count = $offset + 1; // Initialize task counter
                                foreach ($tasks as $task):
                                    $fullTitle = htmlspecialchars($task['title']);
                                    $shortTitle = mb_substr($fullTitle, 0, 5) . (mb_strlen($fullTitle) > 5 ? '...' : '');
                                    // Calculate description variables ONCE per task
                                    $desc = htmlspecialchars($task['description']);
                                    // Use mb_substr and mb_strlen for proper multi-byte character handling
                                    $shortDesc = mb_substr($desc, 0, 5) . (mb_strlen($desc) > 5 ? '...' : '');
                                    $cat = htmlspecialchars($task['category_name'] ?? 'Uncategorized');
                                    $shortCat = mb_substr($cat, 0, 5) . (mb_strlen($cat) > 5 ? '...' : '');
                        ?>
                            <tr>
                                <td class="p-2"><?= $count ?></td>
                                <td class="p-2" title="<?= $fullTitle ?>"><?= $shortTitle ?></td>
                                <td class="p-2" title="<?= $desc ?>"><?= $shortDesc ?></td>
                                <td title="<?= $cat ?>"><?= $shortCat ?></td>
                                <td class="p-2"><?= htmlspecialchars($task['status']) ?></td>
                                <td class="p-2"><?= htmlspecialchars($task['due_date']) ?></td>
                                <td class="p-2"><?= $task['created_at'] ?></td>
                                <td class="p-2"><?= $task['updated_at'] ?></td>
                                <td class="p-2">
                                    <button type="button" class="btn btn-success btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editTaskModal_<?= $task['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteTask(<?= $task['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            
                        <?php include './modals/edit-task-modal.php'; ?>

                        <?php
                                    $count++; // Increment count for the next row
                                endforeach;
                            } else {
                                echo "<tr><td colspan='9' class='text-center p-3'>No tasks available</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            <!-- Task Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Button -->
                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $currentPage - 1 ?>&cat_page=<?= $currentPage ?>#tasks">Previous</a>
                        </li>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link pagination-link" href="?page=<?= $i ?>&cat_page=<?= $currentPage ?>#tasks"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                        <!-- Next Button -->
                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $currentPage + 1 ?>&cat_page=<?= $currentPage ?>#tasks">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            </div>
        </div>

            <!-- Categories -->
            <div class="tab-pane fade" id="category" role="tabpanel" aria-labelledby="category-tab">
                    
                    <!-- Header Section -->
                    <div class="container-fluid d-flex justify-content-between align-items-center my-1">
                        <h4><strong>Task Categories</strong></h4>
                            
                        <!-- Add Category Button -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                            Add Category
                        </button>
                    </div>

                    <!-- Category Table -->
                     <div class="container" id="container2">
                        <table class="table table-bordered table-hover text-center">
                            <thead class="table-dark">
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
                                <?php if (!empty($categories)):
                                    $count = $categoryOffset + 1; // Initialize category counter
                                    foreach ($categories as $category):
                                        // Calculate description variables ONCE per category
                                        $desc = htmlspecialchars($category['description']);
                                        // Use mb_substr and mb_strlen for proper multi-byte character handling
                                        $shortDesc = mb_substr($desc, 0, 10) . (mb_strlen($desc) > 10 ? '...' : '');
                                ?>
                                        <tr>
                                            <td class="p-2"><?= $count ?></td>
                                            <td class="p-2"><?= htmlspecialchars($category['name']) ?></td>
                                            <td class="p-2" title="<?= $desc ?>"><?= $shortDesc ?></td>
                                            <td class="p-2"><?= $category['created_at'] ?? 'N/A' ?></td>
                                            <td class="p-2"><?= $category['updated_at'] ?? 'N/A' ?></td>
                                            <td class="p-2">
                                                <button type="button" class="btn btn-success btn-sm me-1"
                                                    data-bs-toggle="modal" data-bs-target="#editCategoryModal_<?= $category['id'] ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <button class="btn btn-danger btn-sm" onclick="deleteCategory(<?= $category['id'] ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php include './modals/edit-category-modal.php'; ?>
                                <?php
                                        $count++; // Increment count for the next row
                                    endforeach;
                                else: ?>
                                    <tr><td colspan="6" class="text-center p-3">No categories available</td></tr>
                                <?php endif; ?>

                            </tbody>
                        </table>

                        <?php include './modals/add-category-modal.php'; ?>


                    <!-- Pagination -->
                    <?php if ($totalCategoryPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <!-- Previous Button -->
                                <li class="page-item <?= $categoryPage <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $categoryPage - 1 ?>#category">Previous</a>
                                </li>

                                <!-- Page Numbers -->
                                <?php for ($i = 1; $i <= $totalCategoryPages; $i++): ?>
                                    <li class="page-item <?= $i == $categoryPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $i ?>#category"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next Button -->
                                <li class="page-item <?= $categoryPage >= $totalCategoryPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $categoryPage + 1 ?>#category">Next</a>
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
<!-- Bootstrap JS (Required for Modal) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 JS (with Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- JavaScript for Deleting Task with SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="./js/task.js"></script>
</body>
</html>