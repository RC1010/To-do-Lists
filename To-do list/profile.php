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

// Fetch user info
$userId = $_SESSION['user_id'];
try {
    $stmt = $db->prepare("SELECT first_name, last_name, email, profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $email = $user['email'];
        $profile_image = $user['profile_image'] ?? '';
        $_SESSION['user']['profile_image'] = $profile_image;
    } else {
        $_SESSION['response'] = ['success' => false, 'message' => 'User not found.'];
    }
} catch (PDOException $e) {
    $_SESSION['response'] = ['success' => false, 'message' => 'Error fetching profile: ' . $e->getMessage()];
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
                                <img class="profile-image rounded-circle"
                                    src="<?php echo $profile_image ? '/uploads/' . 
                                    htmlspecialchars($profile_image) : '/img/avatar.png'; ?>"
                                    alt="Profile Image" width="150" height="150">
                                <h3 class="mt-3"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h3>
                                <p><?php echo htmlspecialchars($email); ?></p>
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
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Lastname:</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email:</label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" readonly>
                                    </div>
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

<!-- Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="./database/update-profile.php" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3 text-center">
              <input type="file" name="profile_image" class="form-control mt-2">
          </div>
          <div class="mb-3">
              <label>First Name</label>
              <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
          </div>
          <div class="mb-3">
              <label>Last Name</label>
              <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
          </div>
          <div class="mb-3">
              <label>Email</label>
              <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="update_profile" class="btn btn-success">Save changes</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
