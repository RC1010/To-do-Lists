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

// ✅ UPDATED: Priority count logic (count from all tasks, not just current page)
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
                <div class="container" id="tasks">
                    <table class="table table-bordered table-hover text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tasks)): ?>
                                <?php $count = $offset + 1; ?>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td><?= $count++ ?></td>
                                        <td><?= htmlspecialchars($task['title']) ?></td>
                                        <td><?= htmlspecialchars($task['description']) ?></td>
                                        <td><?= htmlspecialchars($task['status']) ?></td>
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
                                        <td><?= $task['created_at'] ?></td>
                                        <td><?= $task['updated_at'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7">No tasks found.</td></tr>
                            <?php endif; ?>
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
</main>
</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', () => {
  function ajaxPaginate(e) {
    e.preventDefault();
    const url = this.href;

    fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => response.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const newContent = doc.querySelector('#tasks');
      const oldContent = document.querySelector('#tasks');
      if (newContent && oldContent) {
        oldContent.innerHTML = newContent.innerHTML;
        history.pushState(null, '', url);
        attachPaginationListeners(); // Reattach events
      }
    })
    .catch(error => console.error('AJAX pagination error:', error));
  }

  function attachPaginationListeners() {
    document.querySelectorAll('.pagination a').forEach(link => {
      link.removeEventListener('click', ajaxPaginate);
      link.addEventListener('click', ajaxPaginate);
    });
  }

  attachPaginationListeners();

  // Optional: Handle browser back/forward
  window.addEventListener('popstate', () => {
    fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(response => response.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector('#tasks');
        const oldContent = document.querySelector('#tasks');
        if (newContent && oldContent) {
          oldContent.innerHTML = newContent.innerHTML;
          attachPaginationListeners();
        }
      });
  });
});
</script>
