<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="profile.php" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

          <!-- Profile Image Upload -->
          <div class="mb-3 text-center">
              <input type="file" name="prof_image" class="form-control mt-2">
          </div>

          <!-- First Name -->
          <div class="mb-3">
              <label>First Name</label>
              <input type="text" name="first_name" class="form-control" 
                value="<?php echo htmlspecialchars($first_name); ?>" required>
          </div>

          <!-- Last Name -->
          <div class="mb-3">
              <label>Last Name</label>
              <input type="text" name="last_name" class="form-control" 
                value="<?php echo htmlspecialchars($last_name); ?>" required>
          </div>

          <!-- Email -->
          <div class="mb-3">
              <label>Email</label>
              <input type="email" name="email" class="form-control"
                value="<?php echo htmlspecialchars($email); ?>" required>
          </div>

          <!-- Old Password -->
          <div class="mb-3 position-relative">
              <label>Current Password</label>
              <input type="password" id="current-password" name="current_password" class="form-control">
              <i class='bx bx-hide eye-icon position-absolute'
                onclick="togglePassword('current-password')"
                style="right: 10px; top: 65%; cursor: pointer;"></i>
          </div>  

          <!-- New Password -->
          <div class="mb-3 position-relative">
              <label class="form-label">New Password</label>
              <input type="password" id="new-password" name="new_password" class="form-control">
              <i class='bx bx-hide eye-icon position-absolute'
                onclick="togglePassword('new-password')"
                style="right: 10px; top: 65%; cursor: pointer;"></i>
          </div>

          <!-- Confirm Password -->
          <div class="mb-3 position-relative">
              <label for="confirm-password" class="form-label">Confirm Password</label>
              <input type="password" id="confirm-password" name="confirm_password" class="form-control">
              <i class='bx bx-hide eye-icon position-absolute'
                onclick="togglePassword('confirm-password')"
                style="right: 10px; top: 65%; cursor: pointer;"></i>
          </div>

          <!-- Password Mismatch Alert -->
          <div class="alert alert-danger alert-dismissible fade show" role="alert" id="password-error" style="display: none;">
              <strong>Passwords do not match.</strong>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
      </div>

      <div class="modal-footer">
        <button type="submit" name="update_profile" class="btn btn-success">Save changes</button>
      </div>
    </form>
  </div>
</div>
<script>
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);
}
</script>
<script>
// Auto-close alert after 5 seconds (5000 ms)
setTimeout(function () {
    const alert = document.getElementById('passwordAlert');
    if (alert) {
        // Bootstrap 5 way to close the alert
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
    }
}, 5000);
</script>
