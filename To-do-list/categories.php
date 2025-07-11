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

// Pagination setup for Tasks
$tasksPerPage = 10;
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

// Pagination setup for Categories
$categoriesPerPage = 10;
$categoryPage = isset($_GET['cat_page']) ? max(1, intval($_GET['cat_page'])) : 1;
$categoryOffset = ($categoryPage - 1) * $categoriesPerPage;

// Get total number of categories **for the logged-in user**
$totalCategoryStmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE user_id = :user_id");
$totalCategoryStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$totalCategoryStmt->execute();
$totalCategories = $totalCategoryStmt->fetchColumn();
$totalCategoryPages = ceil($totalCategories / $categoriesPerPage);

// Fetch paginated categories **for the logged-in user**
$categoryStmt = $db->prepare("SELECT id, name, description, created_at, updated_at FROM categories
                              WHERE user_id = :user_id
                              ORDER BY created_at DESC
                              LIMIT :limit OFFSET :offset");
$categoryStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$categoryStmt->bindValue(':limit', $categoriesPerPage, PDO::PARAM_INT);
$categoryStmt->bindValue(':offset', $categoryOffset, PDO::PARAM_INT);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

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
            <div class="container" id="container2">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="task-tab" data-bs-toggle="tab" href="#task" role="tab">Categories Lists</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tasks-tab" data-bs-toggle="tab" href="#tasks" role="tab">Tasks Priority/Status Lists</a>
                    </li>
                </ul>
                
                <!-- Categories -->
                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="task" role="tabpanel" aria-labelledby="task-tab">
                        <h4><strong>Task Categories</strong></h4>

                        <div class="container" id="container3">
                            <table class="table table-bordered table-hover text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="p-2">#</th>
                                        <th class="p-2">Category Name</th>
                                        <th class="p-2">Description</th>
                                        <th class="p-2">Created At</th>
                                        <th class="p-2">Updated At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($categories)):
                                    $count = $categoryOffset + 1; // Initialize category counter
                                    foreach ($categories as $category):
                                        $desc = htmlspecialchars($category['description']);
                                        $shortDesc = mb_substr($desc, 0, 10) . (mb_strlen($desc) > 10 ? '...' : '');
                                    ?>
                                    <tr>
                                        <td class="p-2"><?= $count++ ?></td>
                                        <td class="p-2"><?= htmlspecialchars($category['name']) ?></td>
                                        <td class="p-2" title="<?= $desc ?>"><?= $shortDesc ?></td>
                                        <td class="p-2"><?= $category['created_at'] ?? 'N/A' ?></td>
                                        <td class="p-2"><?= $category['updated_at'] ?? 'N/A' ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                        <tr>
                                            <td colspan="5" class="p-2 text-center">No categories available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        
                            <!-- Pagination -->
                            <?php if ($totalCategoryPages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <!-- Previous Button -->
                                        <li class="page-item <?= $categoryPage <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $categoryPage - 1 ?>#task">Previous</a>
                                        </li>

                                        <!-- Page Numbers -->
                                        <?php for ($i = 1; $i <= $totalCategoryPages; $i++): ?>
                                            <li class="page-item <?= $i == $categoryPage ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $i ?>#task"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Next Button -->
                                        <li class="page-item <?= $categoryPage >= $totalCategoryPages ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $categoryPage + 1 ?>#task">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Task Status -->
                    <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
                        <div class="task-status">
                            <div class="container-fluid d-flex justify-content-between align-items-center">
                                <h4><strong>Task Status</strong></h4>
                            </div>

                            <div class="container" id="container3">
                                <table class=" table table-bordered table-hover text-center">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="p-2">#</th>
                                            <th class="p-2">Title</th>
                                            <th class="p-2">Category</th>
                                            <th class="p-2">Task Status</th>
                                            <th class="p-2">Task Priority</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($tasks)):
                                            $count = $offset + 1;
                                            foreach ($tasks as $task): ?>
                                                <tr>
                                                    <td class="p-2"><?= $count++ ?></td>
                                                    <td class="p-2"><?= htmlspecialchars($task['title']) ?></td>
                                                    <td class="p-2"><?= htmlspecialchars($task['category_name']) ?></td>
                                                    <td class="p-2"><?= htmlspecialchars($task['status']) ?></td>
                                                    <td>
                                                        <?php
                                                            $badge = 'secondary';
                                                            if (strtolower($task['priority']) === 'high') {
                                                                $badge = 'danger';   // Red
                                                            } elseif (strtolower($task['priority']) === 'medium') {
                                                                $badge = 'warning';  // Yellow
                                                            } elseif (strtolower($task['priority']) === 'low') {
                                                                $badge = 'success';  // Green
                                                            }
                                                        ?>
                                                        <span class="badge bg-<?= $badge ?> text-dark"><?= htmlspecialchars($task['priority']) ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; else: ?>
                                                <tr>
                                                    <td colspan="5" class="p-2 text-center">No tasks available</td>
                                                </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            
                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <nav aria-label="Page navigation" class="mt-4">
                                        <ul class="pagination justify-content-center">
                                            <!-- Previous Button -->
                                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                                <a class="page-link" href="?page=<?= $currentPage - 1 ?>&cat_page=<?= $categoryPage ?>#tasks">Previous</a>
                                            </li>

                                            <!-- Page Numbers -->
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                                    <a class="page-link pagination-link" href="?page=<?= $i ?>&cat_page=<?= $categoryPage ?>#tasks"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <!-- Next Button -->
                                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                                <a class="page-link" href="?page=<?= $currentPage + 1 ?>&cat_page=<?= $categoryPage ?>#tasks">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                <?php endif; ?>   
                            </div>  
                        </div>         
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Bootstrap JS (Required for Modal) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="./js/categories.js"></script>
</body>
</html>