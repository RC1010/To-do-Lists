<?php
ob_start();
session_start();
require_once('./database/connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");  
    exit();
}

$userId = $_SESSION['user_id'];

// Get DB connection
$conn = new Connection();
$db = $conn->getConnection();

// Initialize variables
$first_name = '';
$last_name = '';
$email = '';
$profile_image = '';

try {
    // Fetch user profile
    $stmt = $db->prepare("SELECT first_name, last_name, email, password, profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $email = $user['email'];
        $profile_image = $user['profile_image'] ?? '';
        $_SESSION['user']['profile_image'] = $profile_image;
        $password_hash = $user['password'];

    } else {
        $_SESSION['response'] = ['success' => false, 'message' => 'User not found.'];
    }
} catch (PDOException $e) {
    $_SESSION['response'] = ['success' => false, 'message' => 'Error fetching profile: ' . $e->getMessage()];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $current_timestamp = date('Y-m-d H:i:s');

    // ===== Update Password =====
    if (!empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {

        if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            $_SESSION['response'] = ['success' => false, 'message' => 'Please fill in all password fields.'];
            header('Location: profile.php');
            exit();
        }

        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (!password_verify($current_password, $password_hash)) {
            $_SESSION['response'] = ['success' => false, 'message' => 'Incorrect current password.'];
            header('Location: profile.php');
            exit();
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['response'] = ['success' => false, 'message' => 'New passwords do not match!'];
            header('Location: profile.php');
            exit();
        }

        if (strlen($new_password) < 8 ||
            !preg_match('/[A-Z]/', $new_password) ||
            !preg_match('/[0-9]/', $new_password) ||
            !preg_match('/[\W_]/', $new_password)) {
            $_SESSION['response'] = ['success' => false, 'message' => 'Password must be at least 8 characters, include 1 uppercase letter, 1 number, and 1 special character.'];
            header('Location: profile.php');
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update_stmt = $db->prepare("UPDATE users SET password = :password, updated_at = :updated_at WHERE id = :id");
        $update_stmt->bindParam(':password', $hashed_password);
        $update_stmt->bindParam(':updated_at', $current_timestamp);
        $update_stmt->bindParam(':id', $userId);

        if ($update_stmt->execute()) {
            $_SESSION['response'] = ['success' => true, 'message' => 'Password updated successfully.'];
        } else {
            $_SESSION['response'] = ['success' => false, 'message' => 'Error updating password.'];
        }

        header('Location: profile.php');
        exit();
    }

    // ===== Update Profile Info =====
    if (isset($_POST['first_name'], $_POST['last_name'])) {
        $new_first_name = trim($_POST['first_name']);
        $new_last_name = trim($_POST['last_name']);

        if (empty($new_first_name) || empty($new_last_name)) {
            $_SESSION['response'] = ['success' => false, 'message' => 'First name and last name are required!'];
            header('Location: profile.php');
            exit();
        }

        if (!empty($_FILES['prof_image']['name'])) {
            $targetDir = "uploads/";
            $fileName = basename($_FILES['prof_image']['name']);
            $targetFilePath = $targetDir . time() . '_' . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['response'] = ['success' => false, 'message' => 'Only JPG, JPEG, PNG, and GIF files are allowed.'];
                header('Location: profile.php');
                exit();
            }

            if (move_uploaded_file($_FILES['prof_image']['tmp_name'], $targetFilePath)) {
                $profile_image = basename($targetFilePath);   // ✅ Save only the file name like "1720665000_myphoto.png"
            } else {

                $_SESSION['response'] = ['success' => false, 'message' => 'Error uploading profile image.'];
                header('Location: profile.php');
                exit();
            }
        } else {
            $profile_image = $user['profile_image'];
        }

        $update_stmt = $db->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, profile_image = :profile_image, updated_at = :updated_at WHERE id = :id");
        $update_stmt->bindParam(':first_name', $new_first_name);
        $update_stmt->bindParam(':last_name', $new_last_name);
        $update_stmt->bindParam(':profile_image', $profile_image);
        $update_stmt->bindParam(':updated_at', $current_timestamp);
        $update_stmt->bindParam(':id', $userId);
        $update_stmt->execute();

        $_SESSION['response'] = ['success' => true, 'message' => 'Profile updated successfully.'];
        header('Location: profile.php');
        exit();
    }

    // ===== Update Email =====
    if (!empty($_POST['email'])) {
        $new_email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

        $check_email_stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $check_email_stmt->bindParam(':email', $new_email);
        $check_email_stmt->bindParam(':id', $userId);
        $check_email_stmt->execute();

        if ($check_email_stmt->fetch()) {
            $_SESSION['response'] = ['success' => false, 'message' => 'Email is already in use by another account.'];
            header('Location: profile.php');
            exit();
        }

        $update_email_stmt = $db->prepare("UPDATE users SET email = :email, updated_at = :updated_at WHERE id = :id");
        $update_email_stmt->bindParam(':email', $new_email);
        $update_email_stmt->bindParam(':updated_at', $current_timestamp);
        $update_email_stmt->bindParam(':id', $userId);
        $update_email_stmt->execute();

        $_SESSION['response'] = ['success' => true, 'message' => 'Email updated successfully.'];
        header('Location: profile.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'header/head.php'; ?>
<link rel="stylesheet" href="./css/profile.css">

<body class="bg-light">
<main>
<div id="content">
    <?php include 'header/nav.php'; ?>

    <div class="main-container">
        <h3 class="fw-bold">Profile</h3>

        <!-- Alert -->
        <?php if (isset($_SESSION['response'])): ?>
            <div class="alert alert-<?= $_SESSION['response']['success'] ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['response']['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['response']); ?>
        <?php endif; ?>

        <div class="container" id="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="row border rounded p-5">

                        <!-- Left: Profile Info -->
                        <div class="col-md-5 d-flex flex-column align-items-center justify-content-center text-center">
                            <div class="profile">
                                <img class="prof-image rounded-circle"
                                    src="<?php echo !empty($profile_image) ? '/uploads/' . htmlspecialchars($profile_image) : '/img/avatar.png'; ?>"
                                    alt="Profile Image">
                                <div class="profile-info">
                                    <h3 class="name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h3>
                                    <p class="email"><?php echo htmlspecialchars($email); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Vertical Line -->
                        <div class="col-md-1 d-flex align-items-center">
                            <div class="vr mx-auto bg-black" style="height: 100%; width: 2px;"></div>
                        </div>

                        <!-- Right: Edit info -->
                        <div class="col-md-5 d-flex flex-column justify-content-center">
                            <div class="profile-edit w-100">
                                <div class="form-style text-start"> <!-- Ensures left alignment -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Name:</label>
                                        <input type="text" class="form-control" 
                                            value="<?php echo htmlspecialchars($first_name); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Lastname:</label>
                                        <input type="text" class="form-control" 
                                            value="<?php echo htmlspecialchars($last_name); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email:</label>
                                        <input type="email" class="form-control" 
                                            value="<?php echo htmlspecialchars($email); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Password:</label>
                                        <input type="password" class="form-control" value="********" readonly>
                                    </div>
                                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<?php include './modals/edit-profile-modal.php'; ?>

</body>
</html>
