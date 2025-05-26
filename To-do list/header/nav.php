<?php
// Check if the user is logged in and session variables exist
if (isset($_SESSION['users'])) {
    $first_name = $_SESSION['users']['first_name'];
    $last_name = $_SESSION['users']['last_name'];
    $email = $_SESSION['users']['email'];
} else {
    // Default values if no user is logged in
    $first_name = "Guest";
    $last_name = "";
    $email = "";
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include 'head.php'; ?>
<link rel="stylesheet" href="../css/nav.css">

<body class="bg-light">

<!-- Content Section (For AJAX loading) -->
<div id="content">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-5">
        <a class="navbar-brand me-auto">
            <h2 class= "px-5"><strong>To-do Plan</strong></h2>
        </a>

    <div class="flex-grow-1 d-flex justify-content-center">
        <form class="d-flex w-50">
            <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search">
            <button class="btn btn-outline-light" type="submit">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>

     <!-- Right: Date & Notification -->
     <div class="d-flex align-items-center">
        <button class="btn btn-outline-light me-3" type="button">
            <i class="bi bi-calendar3"></i> 
        </button>

        <button class="btn btn-outline-light me-3" type="button">
            <i class="bi bi-bell"></i>
        </button>

        <div class="d-flex align-items-center flex-column text-white me-3">
            <span id="realTimeDay" class="fw-bold"></span> <!-- Day of the Week -->
            <span id="realTimeDate"></span> <!-- Date -->
        </div>
    </div>
    </nav>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar bg-dark text-white">
        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">

            <div class="sidebar-profile">
                <img class="profile-image rounded-circle" src="../img/avatar.png" alt="Profile Image">
                <h4><?php echo htmlspecialchars($first_name. '' .$last_name); ?></h4>
                <p><?php echo htmlspecialchars($email); ?></p>
            </div>

            <a class="nav-link" href="dashboard.php">
                <i class="bi bi-grid"></i> 
                <span class="ms-2">Dashboard</span> 
            </a>

            <a class="nav-link" href="profile.php">
                <i class="bi bi-person-circle"></i> 
                <span class="ms-2">Profile</span>
            </a>

            <!-- Dropdown for To-do -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="toDoDropdown" role="button" aria-expanded="false">
                    <i class="bi bi-card-checklist"></i> 
                    <span class="ms-2">To-do</span>
                </a>
                <ul class="dropdown-menu bg-dark text-white" aria-labelledby="toDoDropdown">
                    <li><a class="dropdown-item text-white" href="task.php">
                        <i class="bi bi-card-list"></i> 
                        <span class="ms-2">My Task</span>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-white" href="#">
                        <i class="bi bi-star"></i>
                        <span class="ms-2">Vital Task</span>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-white" href="categories.php">
                        <i class="bi bi-list-task"></i>
                        <span class="ms-2">Task Categories</span>
                    </a></li>
                </ul>
            </div>

            <a class="nav-link" href="settings.php">
                <i class="bi bi-gear"></i> 
                <span class="ms-2">Settings</span>
            </a>

            <a class="nav-link logout-btn" href="../database/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span class="ms-2">Log out</span>
            </a>

        </div>
    </aside>

    <!-- Button to toggle sidebar -->
    <button class="toggle-btn bg-dark text-white" id="toggleSidebar">
        <i class="bi bi-list"></i> 
    </button>


<!-- Bootstrap JS -->
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
                e.preventDefault(); // Prevent the default link action
                menu.classList.toggle('show'); // Toggle the dropdown menu visibility
            });

            // Close the dropdown when clicking outside
            document.addEventListener('click', function (event) {
                if (!dropdown.contains(event.target)) {
                    menu.classList.remove('show');
                }
            });
        });
    });

    // Real-Time Date
    function updateDate() {
        let now = new Date();

        // Get the day (e.g., "Friday")
        let day = now.toLocaleDateString('en-US', { 
            timeZone: 'Asia/Singapore', 
            weekday: 'long' 
        });

        // Get the date (e.g., "03/14/2025")
        let date = now.toLocaleDateString('en-US', { 
            timeZone: 'Asia/Singapore', 
            month: '2-digit', 
            day: '2-digit', 
            year: 'numeric' 
        });

        // Set the content
        document.getElementById('realTimeDay').textContent = day; // Display Day
        document.getElementById('realTimeDate').textContent = date; // Display Date
    }

    // Call the function initially and then update every second
    updateDate();
    setInterval(updateDate, 1000);

</script>
</div>
</body>
</html>
