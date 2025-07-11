<?php
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once('./database/connection.php');

// Fetch user info
$first_name = $last_name = $email = $profile_image = '';
$userId = $_SESSION['user_id'];

try {
    $conn = new Connection();
    $db = $conn->getConnection();
    
    $stmt = $db->prepare("SELECT first_name, last_name, email, profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $email = $user['email'];
        $profile_image = $user['profile_image'] ?? '';
    }
} catch (PDOException $e) {
    $first_name = "Error";
    $last_name = "";
    $email = "Could not load";
}

// Fetch upcoming due date notifications
try {
    $today = date('Y-m-d');
    $nextWeek = date('Y-m-d', strtotime('+7 days'));

    $notif_stmt = $db->prepare("SELECT title, due_date FROM tasks WHERE user_id = :user_id AND due_date BETWEEN :today AND :nextWeek ORDER BY due_date ASC");
    $notif_stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $notif_stmt->bindParam(':today', $today);
    $notif_stmt->bindParam(':nextWeek', $nextWeek);
    $notif_stmt->execute();

    $notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $notifications = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'head.php'; ?>
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<link rel="stylesheet" href="../css/nav.css">

<body class="bg-light">

<!-- Content Section -->
<div id="content">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-5">
        <a class="navbar-brand me-auto">
            <h2 class="px-5"><strong>To-do Plan</strong></h2>
        </a>

        <div class="flex-grow-1 d-flex justify-content-center">
            <form class="d-flex w-50">
                <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search">
                <button class="btn btn-outline-light" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>

        <div class="d-flex align-items-center">
            <div class="input-group">
                <span class="input-group-text btn btn-outline-light me-3" id="calendar-icon">
                    <i class="bi bi-calendar3"></i>
                </span>
            </div>

            <div id="calendarContainer" class="d-none border p-2 rounded bg-white shadow"
                style="z-index: 2000; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            </div>

            <div class="dropdown me-3">
                <button class="btn btn-outline-light position-relative" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <?php if (!empty($notifications)): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= count($notifications) ?>
                        </span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="notifDropdown" style="min-width: 300px;">
                    <?php if (empty($notifications)): ?>
                        <li class="dropdown-item text-center text-muted">No upcoming tasks.</li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <li class="dropdown-item">
                                <strong><?= htmlspecialchars($notif['title']) ?></strong><br>
                                <small class="text-muted">Due: <?= htmlspecialchars($notif['due_date']) ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="d-flex align-items-center flex-column text-white me-3">
                <span id="navrealTimeDay" class="fw-bold"></span>
                <span id="navrealTimeDate"></span>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar bg-dark text-white">
        <div class="nav flex-column nav-pills" role="tablist">

            <div class="sidebar-profile">
                <img class="profile-image rounded-circle"
                    src="<?php echo $profile_image ? '/uploads/' . htmlspecialchars($profile_image) : '/img/avatar.png'; ?>"
                    alt="Profile Image">
                <h4><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h4>
                <p><?php echo htmlspecialchars($email); ?></p>
            </div>

            <a class="nav-link" href="dashboard.php"><i class="bi bi-grid"></i><span class="ms-2">Dashboard</span></a>
            <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i><span class="ms-2">Profile</span></a>

            <!-- To-do Dropdown -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="toDoDropdown" role="button" aria-expanded="false">
                    <i class="bi bi-card-checklist"></i><span class="ms-2">To-do</span>
                </a>
                <ul class="dropdown-menu bg-dark text-white" aria-labelledby="toDoDropdown">
                    <li><a class="dropdown-item text-white" href="task.php"><i class="bi bi-card-list"></i><span class="ms-2">My Task</span></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-white" href="vital-task.php"><i class="bi bi-star"></i><span class="ms-2">Vital Task</span></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-white" href="categories.php"><i class="bi bi-list-task"></i><span class="ms-2">Task Categories</span></a></li>
                </ul>
            </div>

            <a class="nav-link" href="settings.php"><i class="bi bi-gear"></i><span class="ms-2">Settings</span></a>
            <a class="nav-link logout-btn" href="../database/logout.php"><i class="bi bi-box-arrow-right"></i><span class="ms-2">Log out</span></a>
        </div>
    </aside>

    <!-- Sidebar Toggle Button -->
    <button class="toggle-btn bg-dark text-white" id="toggleSidebar"><i class="bi bi-list"></i></button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        document.addEventListener("DOMContentLoaded", function () {
            var dropdowns = document.querySelectorAll('.nav-item.dropdown');
            dropdowns.forEach(function (dropdown) {
                var toggle = dropdown.querySelector('.dropdown-toggle');
                var menu = dropdown.querySelector('.dropdown-menu');

                toggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    menu.classList.toggle('show');
                });

                document.addEventListener('click', function (event) {
                    if (!dropdown.contains(event.target)) {
                        menu.classList.remove('show');
                    }
                });
            });

            function updateDate() {
                let now = new Date();
                let day = now.toLocaleDateString('en-US', { timeZone: 'Asia/Singapore', weekday: 'long' });
                let date = now.toLocaleDateString('en-US', { timeZone: 'Asia/Singapore', month: '2-digit', day: '2-digit', year: 'numeric' });

                document.getElementById('navrealTimeDay').textContent = day;
                document.getElementById('navrealTimeDate').textContent = date;
            }

            updateDate();
            setInterval(updateDate, 1000);
        });
</script>
<script>
// Init flatpickr calendar
document.addEventListener("DOMContentLoaded", function () {
  const calendarIcon = document.getElementById("calendar-icon");
  const calendarContainer = document.getElementById("calendarContainer");

  flatpickr(calendarContainer, {
    inline: true,
    appendTo: calendarContainer, // Ensures calendar stays inside this div
  });

  // Toggle visibility on calendar icon click
  calendarIcon.addEventListener("click", function (event) {
    calendarContainer.classList.toggle("d-none");
    event.stopPropagation();
  });

  // Hide calendar when clicking outside
  document.addEventListener("click", function (event) {
    if (!calendarContainer.contains(event.target) && !calendarIcon.contains(event.target)) {
      calendarContainer.classList.add("d-none");
    }
  });
});

</script>
</div>
</body>
</html>