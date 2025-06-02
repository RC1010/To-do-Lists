<?php
session_start();

// Check if the user is NOT logged in
if (!isset($_SESSION['users'])) {
    header('Location: index.php'); // Redirect to the login page
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<?php include 'header/head.php'; ?>
<link rel="stylesheet" href="./css/dashboard.css">
    
<body class="bg-light">

<!-- Content Section (For AJAX loading) -->
<div id="content">
    <?php include 'header/nav.php'; ?>
    
</div>
</body>
</html>