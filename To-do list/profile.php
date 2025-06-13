<?php
ob_start();
session_start();
require_once('./database/connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");  
    exit();
}

$conn = new Connection();
$db = $conn->getConnection();


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
                        <div class="col-md-5 d-flex-column justify-content-center text-center">
                            <div class="profile">
                                <img class="profile-image rounded-circle"
                                    src="<?php echo isset($_SESSION['user']['profile_image']) ? '../uploads/' . htmlspecialchars($_SESSION['user']['profile_image']) : './img/avatar.png'; ?>"
                                    alt="Profile Image">
                                <h3><?php echo htmlspecialchars($first_name. '' .$last_name); ?></h3>
                                <p><?php echo htmlspecialchars($email); ?></p>

                            </div>
                        </div>

                        <!-- Vertical Line -->
                        <div class="col-md-1 d-flex align-items-center">
                            <div class="vr mx-auto bg-black" style="height: 100%; width: 2px;"></div>
                        </div>

                        <!-- Right: Edit info -->
                         <div class="col-md-5">
                            <div class="col-md-5 d-flex flex-column justify-content-center text-center">
                                <div class="profile-edit">                                    
                                    <div class="form-style">
                                        <h5>
                                            Name: <input type="text" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" readonly>
                                        </h5>

                                        <h5>
                                            Lastname: <input type="text" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" readonly>
                                        </h5>

                                        <h5>
                                            Email: <input type="email" class="form-control mt-2" value="<?php echo htmlspecialchars($email); ?>" readonly>
                                        </h5>
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