<?php
session_start();

// Check if the user is NOT logged in
if (!isset($_SESSION['users'])) {
    header('Location: index.php'); // Redirect to the login page
    exit();
}

// Get the first name from session
$first_name = $_SESSION['users']['first_name'];
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'header/head.php'; ?>
<link rel="stylesheet" href="./css/dashboard.css">
    
<body class="bg-light">

<!-- Content Section (For AJAX loading) -->
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

    </main>
</div>
</body>
</html>