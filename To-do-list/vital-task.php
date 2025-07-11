<?php
ob_start(); // Prevent header issues
session_start();
require_once './database/connection.php';

if (!isset($_SESSION['users'])) {
    header('Location: index.php');
    exit();
}

// Create an instance of the Connection class
$connObj = new Connection();
$conn = $connObj->getConnection(); // Get the actual PDO connection

// Pagination setup
$tasksPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $tasksPerPage;
$user_id = $_SESSION['users']['id'];

// Get total task count for user
$totalStmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
$totalStmt->execute([$user_id]);
$totalTasks = $totalStmt->fetchColumn();
$totalPages = ceil($totalTasks / $tasksPerPage);

// Priority count logic (count from all tasks, not just current page)
$priorityStmt = $conn->prepare("
    SELECT priority, COUNT(*) as count
    FROM tasks
    WHERE user_id = :user_id
    GROUP BY priority
");
$priorityStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$priorityStmt->execute();

$highCount = $mediumCount = $lowCount = 0;
while ($row = $priorityStmt->fetch(PDO::FETCH_ASSOC)) {
    $priority = strtolower(trim($row['priority']));
    if ($priority === 'high') $highCount = $row['count'];
    elseif ($priority === 'medium') $mediumCount = $row['count'];
    elseif ($priority === 'low') $lowCount = $row['count'];
}

// Fetch paginated tasks for the current user
$stmt = $conn->prepare("
    SELECT t.*, c.name AS category_name
    FROM tasks t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :user_id
    ORDER BY 
        CASE 
            WHEN priority = 'High' THEN 1
            WHEN priority = 'Medium' THEN 2
            WHEN priority = 'Low' THEN 3
            ELSE 4
        END,
        t.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $tasksPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'header/head.php'; ?>
<link rel="stylesheet" href="./css/vital-task.css">

<body class="bg-light">
<main>
<div id="content">
    <?php include 'header/nav.php'; ?>

    <div class="main-container">
        <div class="container" id="container">
            <h2 class="mb-4 text-center"><strong>Vital Tasks </strong></h2>

            <!-- Priority Count Summary -->
            <div class="text-center mx-auto mb-4">
                <span class="mx-2">
                    <strong>🔴 High:</strong> <span class="text-dark"><?= $highCount ?></span>
                </span>
                <span class="mx-2">
                    <strong>🟡 Medium:</strong> <span class="text-dark"><?= $mediumCount ?></span>
                </span>
                <span class="mx-2">
                    <strong>🟢 Low:</strong> <span class="text-dark"><?= $lowCount ?></span>
                </span>
                <span class="mx-2">
                    <strong>Total:</strong> <span class="text-dark"><?= $totalTasks ?></span>
                </span>
            </div>
            
            <div class="container" id="container2">
                <!-- Task Table -->
                <div class="table-responsive">
                    <div class="container" id="tasks">
                        <table class="table table-bordered table-hover text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if (!empty($tasks)) {
                                $count = $offset + 1;
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
                                    <td><?= $count ?></td>
                                    <td class="p-2" title="<?= $fullTitle ?>"><?= $shortTitle ?></td>
                                    <td class="p-2"title="<?= $desc ?>"><?= $shortDesc ?></td>
                                    <td title="<?= $cat ?>"><?= $shortCat ?></td>
                                    <td><?= htmlspecialchars($task['status']) ?></td>
                                    <td>
                                        <?php
                                            $badge = 'secondary';
                                            if (strtolower($task['priority']) === 'high') $badge = 'danger';
                                            elseif (strtolower($task['priority']) === 'medium') $badge = 'warning';
                                            elseif (strtolower($task['priority']) === 'low') $badge = 'success';
                                        ?>
                                        <span class="badge bg-<?= $badge ?> text-dark"><?= htmlspecialchars($task['priority']) ?></span>
                                    </td>
                                    <td class="p-2"><?= htmlspecialchars($task['due_date']) ?></td>
                                    <td><?= $task['created_at'] ?></td>
                                    <td><?= $task['updated_at'] ?></td>
                                </tr>
                                <?php include './modals/edit-task-modal.php'; ?>
                            <?php
                                    $count++;
                                endforeach;
                            } else {
                                echo "<tr><td colspan='7' class='text-center p-3'>No tasks available</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-3">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>#tasks">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>#tasks"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>#tasks">Next</a>
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
<script src="./js/vital-task.js"></script>
</body>
</html>
