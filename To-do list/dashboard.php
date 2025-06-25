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
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Real-Time Date
    function updateDate() {
        let now = new Date();

        // Get the date (e.g., "03/14/2025")
        let date = now.toLocaleDateString('en-US', { 
            timeZone: 'Asia/Singapore', 
            month: '2-digit', 
            day: '2-digit', 
        });

        // Set the content
        document.getElementById('realTimeDate').textContent = date; // Display Date
    }

    // Call the function initially and then update every second
    updateDate();
    setInterval(updateDate, 1000);
});
</script>

<script>
// custom script for doughnut
Chart.register({
    id: 'centerTextPlugin',
    beforeDraw(chart) {
        const {width} = chart;
        const {height} = chart;
        const ctx = chart.ctx;
        const datasets = chart.data.datasets[0].data;

        // Only apply to doughnut
        if (chart.config.type === 'doughnut') {
            const total = datasets.reduce((a, b) => a + b, 0);
            const value = datasets[0];
            const percentage = ((value / total) * 100).toFixed(0) + '%';

            ctx.save();
            ctx.font = 'bold 18px Arial';
            ctx.textBaseline = 'middle';
            ctx.textAlign = 'center';
            ctx.fillStyle = '#333';
            ctx.fillText(percentage, width / 2, height / 2);
            ctx.restore();
        }
    }
});
// Task Progress
document.addEventListener('DOMContentLoaded', () => {
    const completed = <?= $completedCount ?>;
    const inProgress = <?= $inProgressCount ?>;
    const notStarted = <?= $notStartedCount ?>;
    const totalTasks = <?= $totalTasks ?>;

    // Reusable function to draw individual percentage charts
    function createStatusChart(canvasId, count, color) {
        const remaining = totalTasks - count;
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
                                const percentage = ((context.parsed / totalTasks) * 100).toFixed(1);
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

<!-- Ajax reload -->
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
      const newContent = doc.querySelector('#completedTasks');
      const oldContent = document.querySelector('#completedTasks');
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

  // Handle browser back/forward
  window.addEventListener('popstate', () => {
    fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(response => response.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector('#completedTasks');
        const oldContent = document.querySelector('#completedTasks');
        if (newContent && oldContent) {
          oldContent.innerHTML = newContent.innerHTML;
          attachPaginationListeners();
        }
      });
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {

  function ajaxPaginate(e, targetSelector, containerSelector, paginationClass) {
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
      const newContent = doc.querySelector(targetSelector);
      const oldContent = document.querySelector(containerSelector);
      if (newContent && oldContent) {
        oldContent.innerHTML = newContent.innerHTML;
        history.pushState(null, '', url);
        attachPaginationListeners(); // Reattach events
      }
    })
    .catch(error => console.error(`AJAX pagination error (${paginationClass}):`, error));
  }

  function attachPaginationListeners() {
    // Completed
    document.querySelectorAll('.completed-pagination a').forEach(link => {
      link.removeEventListener('click', handleCompletedClick);
      link.addEventListener('click', handleCompletedClick);
    });

    // Pending
    document.querySelectorAll('.pending-pagination a').forEach(link => {
      link.removeEventListener('click', handlePendingClick);
      link.addEventListener('click', handlePendingClick);
    });
  }

  function handleCompletedClick(e) {
    ajaxPaginate.call(this, e, '#completedTasks', '#completedTasks', 'completed');
  }

  function handlePendingClick(e) {
    ajaxPaginate.call(this, e, '#pendingTasks', '#pendingTasks', 'pending');
  }

  attachPaginationListeners();

  // Handle browser back/forward
  window.addEventListener('popstate', () => {
    fetch(location.href, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => response.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      const newCompleted = doc.querySelector('#completedTasks');
      const oldCompleted = document.querySelector('#completedTasks');
      if (newCompleted && oldCompleted) {
        oldCompleted.innerHTML = newCompleted.innerHTML;
      }

      const newPending = doc.querySelector('#pendingTasks');
      const oldPending = document.querySelector('#pendingTasks');
      if (newPending && oldPending) {
        oldPending.innerHTML = newPending.innerHTML;
      }

      attachPaginationListeners();
    });
  });
});
</script>

</body>
</html>