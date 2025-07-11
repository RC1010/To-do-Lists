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

// Get the first name from session
$first_name = $_SESSION['users']['first_name'];

// Task Status
$completedCount = 0;
$inProgressCount = 0;
$notStartedCount = 0;

$stmt = $db->prepare("SELECT status, COUNT(*) as count FROM tasks
                        WHERE user_id = :user_id GROUP BY status");
$stmt->execute(['user_id' => $_SESSION['users']['id']]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $status = strtolower($row['status']);
    if ($status === 'completed') {
        $completedCount = $row['count'];
    } elseif ($status === 'in progress') {
        $inProgressCount = $row['count'];
    } elseif ($status === 'not started') {
        $notStartedCount = $row['count'];
    }
}
$totalTasks = $completedCount + $inProgressCount + $notStartedCount;

// Pagination setup for completed tasks
$completedTasksPerPage = 5;
$completedPage = isset($_GET['completed_page']) ? max(1, intval($_GET['completed_page'])) : 1;
$completedOffset = ($completedPage - 1) * $completedTasksPerPage;

// Get total completed tasks count
$countStmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND LOWER(status) = 'completed'");
$countStmt->execute(['user_id' => $_SESSION['users']['id']]);
$totalCompletedTasks = $countStmt->fetchColumn();
$totalCompletedPages = ceil($totalCompletedTasks / $completedTasksPerPage);

// Fetch paginated completed tasks sorted by most recent
$completedTasksStmt = $db->prepare("SELECT title, description, updated_at 
    FROM tasks 
    WHERE user_id = :user_id AND LOWER(status) = 'completed' 
    ORDER BY updated_at DESC 
    LIMIT :limit OFFSET :offset");

$completedTasksStmt->bindValue(':user_id', $_SESSION['users']['id'], PDO::PARAM_INT);
$completedTasksStmt->bindValue(':limit', $completedTasksPerPage, PDO::PARAM_INT);
$completedTasksStmt->bindValue(':offset', $completedOffset, PDO::PARAM_INT);
$completedTasksStmt->execute();
$completedTasks = $completedTasksStmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination setup for pending tasks
$pendingTasksPerPage = 5;
$pendingPage = isset($_GET['pending_page']) ? max(1, intval($_GET['pending_page'])) : 1;
$pendingOffset = ($pendingPage - 1) * $pendingTasksPerPage;

// Get total pending tasks count
$countPendingStmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND LOWER(status) != 'completed'");
$countPendingStmt->execute(['user_id' => $_SESSION['users']['id']]);
$totalPendingTasks = $countPendingStmt->fetchColumn();
$totalPendingPages = ceil($totalPendingTasks / $pendingTasksPerPage);

// Fetch paginated pending tasks
$pendingTasksStmt = $db->prepare("
    SELECT title, description, due_date, priority 
    FROM tasks 
    WHERE user_id = :user_id AND LOWER(status) != 'completed'
    ORDER BY 
      CASE priority
        WHEN 'High' THEN 1
        WHEN 'Medium' THEN 2
        WHEN 'Low' THEN 3
      END,
      due_date ASC
    LIMIT :limit OFFSET :offset
");

$pendingTasksStmt->bindValue(':user_id', $_SESSION['users']['id'], PDO::PARAM_INT);
$pendingTasksStmt->bindValue(':limit', $pendingTasksPerPage, PDO::PARAM_INT);
$pendingTasksStmt->bindValue(':offset', $pendingOffset, PDO::PARAM_INT);
$pendingTasksStmt->execute();

$pendingTasks = $pendingTasksStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<?php include 'header/head.php'; ?>
<link rel="stylesheet" href="./css/dashboard.css">
    
<body class="bg-light">

<div id="content">
    <?php include 'header/nav.php'; ?>
    
    <header>
        <div class="alert alert-success w-50 m-5 p-4 mx-auto text-center custom-alert" role="alert">
            <h2 class="alert-heading">
                <strong>Welcome, <?php echo htmlspecialchars($first_name); ?>! 👋</strong>
            </h2>
        </div>
    </header>

    <main>
        <div class="main-container">
            <div class="container" id="container">
                <div class="row align-items-stretch" style="min-height: 100%;">
                    <!-- Left content -->
                    <div class="col-md-5 p-3">
                        <div id="pendingTasks">
                            <?php include 'partials/pending-tasks.php'; ?>
                        </div>

                        <!-- Vertical Line -->
                        <div class="col-md-1 d-flex justify-content-center">
                            <div class="vr bg-black" style="width: 2px; height: 100%;"></div>
                        </div>

                        <!-- Right content -->
                        <div class="col-md-5 p-3">
                            <h3><strong>Task Status</strong></h3>
                            <div class="row text-center my-3">
                                <div class="col-md-4">
                                    <canvas id="completedChart" width="150" height="150"></canvas>
                                    <p class="mt-2">🟢 Completed</p>
                                </div>
                                <div class="col-md-4">
                                    <canvas id="inProgressChart" width="150" height="150"></canvas>
                                    <p class="mt-2">🟡 In Progress</p>
                                </div>
                                <div class="col-md-4">
                                    <canvas id="notStartedChart" width="150" height="150"></canvas>
                                    <p class="mt-2">🔴 Not Started</p>
                                </div>
                            </div>

                            <!-- Horizontal Line -->
                            <div class="col-12 d-flex align-items-center my-3">
                                <div class="bg-secondary mx-auto" style="height: 2px; width: 100%;"></div>
                            </div>

                            <div id="completedTasks">
                                <?php include 'partials/completed-tasks.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="./js/dashboard.js"></script>

<script>
// Task Progress
document.addEventListener('DOMContentLoaded', () => {
    const completed = <?= json_encode($completedCount) ?>;
    const inProgress = <?= json_encode($inProgressCount) ?>;
    const notStarted = <?= json_encode($notStartedCount) ?>;
    const totalTasks = <?= json_encode($totalTasks) ?>;

    // Reusable function to draw individual percentage charts
    function createStatusChart(canvasId, count, color) {
    const safeTotal = totalTasks > 0 ? totalTasks : 1; // Prevent divide by 0
    const remaining = Math.max(0, safeTotal - count);

    new Chart(document.getElementById(canvasId).getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Count', 'Others'],
            datasets: [{
                data: [count, remaining],
                backgroundColor: [color, '#e9ecef'],
                borderWidth: 1
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

    createStatusChart('completedChart', completed, '#28a745');
    createStatusChart('inProgressChart', inProgress, '#ffc107');
    createStatusChart('notStartedChart', notStarted, '#dc3545');
});
</script>



</body>
</html>