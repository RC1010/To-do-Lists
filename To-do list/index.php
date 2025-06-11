<?php
ob_start();
session_start();
require_once './database/connection.php';

$conn = new Connection();
$pdo = $conn->getConnection(); // Get PDO connection only once


// Registration Process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $current_timestamp = date('Y-m-d H:i:s');

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['response'] = ['success' => false, 'message' => 'All fields are required!'];
        header('Location: index.php');
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Passwords do not match!'];
        header('Location: index.php');
        exit();
    }

    // Check if email already exists
    $check_email_stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $check_email_stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $check_email_stmt->execute();

    if ($check_email_stmt->fetch()) {
        $_SESSION['response'] = ['success' => false, 'message' => 'Email is already taken!'];
        header('Location: index.php');
        exit();
    }

    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, password, created_at, updated_at) 
        VALUES (:first_name, :last_name, :email, :password, :created_at, :updated_at)");
    $insert_stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
    $insert_stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
    $insert_stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $insert_stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    $insert_stmt->bindParam(':created_at', $current_timestamp);
    $insert_stmt->bindParam(':updated_at', $current_timestamp);

    if ($insert_stmt->execute()) {
        $_SESSION['response'] = ['success' => true, 'message' => 'User registered successfully. Please log in.'];
    } else {
        $_SESSION['response'] = ['success' => false, 'message' => 'Error adding user!'];
    }

    header('Location: index.php');
    exit();
}

// Login process
if (!empty($_POST['email']) && !empty($_POST['password'])) {
    // Trim and sanitize inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user data
    $user = $conn->loginUser($email, $password);
    
    if ($user && password_verify($password, $user['password'])) {    
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['users'] = $user;
        $_SESSION['first_name'] = $user['first_name']; // Store the first name in session
        
        // Update user status to active
        $pdo = $conn->getConnection();
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = :id");
        $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
        $stmt->execute();

        header('Location: dashboard.php');
        exit();
    } else {
        $_SESSION['response'] = ['success' => false, 'message' => 'Email or password is incorrect'];
        header('Location: index.php');
        exit();
    }
}

ob_end_flush(); // Flush output buffer
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'header/head.php'; ?>
    
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3">To-Do Plan</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse bg-dark" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link me-4" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link me-4" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact Us</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Home Section -->
    <section id="home" class="py-5 bg-white">
        <div class="container">
            <div class="row align-items-center">
                <!-- Home Content on the Left -->
                <div class="col-md-6">
                    <h1 class="fw-bold">Manage Your Day-to-Day Schedule With To-Do Plan!</h1>
                    <p class="mt-3"><b>Stay organized, track tasks, and improve productivity</b>  
                                    with our simple yet effective <b>To-Do Plan</b>.  
                                    Manage your daily schedule effortlessly, set priorities, and  
                                    visualize progress with interactive tracking. Take control of your time  
                                    and achieve your goals with ease!</p>
                </div>

                <!-- Login Form -->
                <div class="col-md-4 ms-auto" id="loginForm">
                    <div class="card shadow p-4">
                        <form action="index.php" method="POST">
                            <label class="form-label fs-4 fw-bold d-block text-center">Login</label>
                            <hr>
                            
                            <!-- If Email or password is wrong -->
                            <?php if (isset($_SESSION['response'])): ?>
                                <div class="mx-auto alert alert-<?= $_SESSION['response']['success'] ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                                    <strong><?= $_SESSION['response']['success'] ? '' : '' ?></strong> 
                                    <?= htmlspecialchars($_SESSION['response']['message']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['response']); // Clear message after displaying ?>
                            <?php endif; ?>

                            <!-- If Registration is Successful/ Incorrect -->
                            <?php if (isset($_SESSION['response'])): ?>
                                <div class=" mx-auto alert alert-<?= $_SESSION['response']['success'] ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                                    <strong><?= $_SESSION['response']['success'] ? '' : '' ?></strong> 
                                    <?= htmlspecialchars($_SESSION['response']['message']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['response']); // Clear message after displaying ?>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="login_email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <i class='bx bx-hide eye-icon position-absolute' onclick="togglePassword('password')" style="right: 10px; top: 65%; cursor: pointer;"></i>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="#" class="text-primary">Forgot password?</a>
                                <a href="#" class="text-primary" onclick="toggleForm()">Signup now!</a>
                            </div>
                            <button type="submit" name="login" class="btn btn-dark w-100 mt-3">Login</button>
                        </form>
                    </div>
                </div>
                
                <!-- Signup Form -->
                <div class="col-md-4 d-none ms-auto" id="signupForm">
                    <div class="card shadow p-4">
                        <form action="index.php" method="POST">
                            <label class="form-label fs-4 fw-bold d-block text-center">Signup</label>
                            <hr>
                            <div class="mb-3">
                                <label for="fname" class="form-label">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="lname" class="form-label">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="signup-email" class="form-label">Email</label>
                                <input type="email" id="signup-email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="signup-password" class="form-label">Password</label>
                                <input type="password" id="signup-password" name="password" class="form-control" required>
                                <i class='bx bx-hide eye-icon position-absolute' onclick="togglePassword('signup-password')" style="right: 10px; top: 65%; cursor: pointer;"></i>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="confirm-password" class="form-label">Confirm Password</label>
                                <input type="password" id="confirm-password" name="confirm_password" class="form-control" required>
                                <i class='bx bx-hide eye-icon position-absolute' onclick="togglePassword('confirm-password')" style="right: 10px; top: 65%; cursor: pointer;"></i>
                            </div>

                            <!-- Password Mismatch Alert -->
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="password-error" style="display: none;">
                                <strong>Password do not match.</strong> 
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="#" class="text-primary" onclick="toggleForm()">Already have an account?</a>
                            </div>
                            <button type="submit" name="signup" class="btn btn-success w-100 mt-3" id="submitBtn">Signup</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container" style="max-width: 850px;">
            <div class="border rounded p-4 shadow-lg bg-light">
                <h2 class="text-center fw-bold">About</h2>
                <hr>
                <p class="text-center lead px-5">A To-Do Plan is a powerful tool for managing your daily schedule, 
                                            organizing tasks, and keeping track of your plans efficiently. 
                                            With a structured to-do list, you can set priorities, schedule 
                                            deadlines, and ensure productivity throughout the day. This 
                                            system also includes an interactive graph that visually represents 
                                            your activity, allowing you to track your progress over time.</p>
                <div class="container border rounded p-2 mt-2 shadow-sm bg-white" style="max-width: 850px;">
                    <div class="row align-items-center">
                        <!-- Left side: h4 title -->
                        <div class="d-flex justify-content-center">
                            <h4 class="fw-bold">Pros of a To-Do Plan:</h4>
                        </div>
                        <!-- Right side: List -->
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>📌 Better Organization:</strong> <br>• Keeps all tasks in one place.</li>
                                    <li class="list-group-item"><strong>✅ Improved Productivity:</strong> <br>• Focus on important tasks.</li>
                                    <li class="list-group-item"><strong>📊 Visual Tracking:</strong> <br>- Graphs show completed & skipped tasks.</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>🔥 Accountability & Motivation:</strong> <br>• Encourages consistency.</li>
                                    <li class="list-group-item"><strong>⏳ Time Management:</strong> <br>• Helps allocate time efficiently.</li>
                                    <li class="list-group-item"><strong>⚡ Flexibility:</strong> <br>• Adjust plans based on priorities.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="row border rounded p-4 shadow-lg bg-light">
                        <!-- Left: Contact Details -->
                        <div class="col-md-5 d-flex flex-column justify-content-center text-center">
                            <h3 class="fw-bold">Contact Us</h3>
                            <p>If you have any questions or need assistance, feel free to reach out to us.</p>
                            <hr>
                            <p><strong>Email:</strong> support@example.com</p>
                            <p><strong>Phone:</strong> +1 (123) 456-7890</p>
                            <p><strong>Address:</strong> 123 Main Street, City, Country</p>
                        </div>

                        <!-- Vertical Line -->
                        <div class="col-md-1 d-flex align-items-center">
                            <div class="vr mx-auto" style="height: 100%; width: 2px; background-color: #ddd;"></div>
                        </div>

                        <!-- Right: Message Form -->
                        <div class="col-md-6">
                            <h4 class="fw-bold text-center">Send Us a Message</h4>
                            <form>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="name" placeholder="Enter your name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Your Email</label>
                                    <input type="email" class="form-control" id="email" placeholder="Enter your email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" rows="4" placeholder="Enter your message" required></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-dark">Send Message</button>
                                </div>
                            </form>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </section>


    <!-- Footer Section -->
    <footer class="text-light bg-dark text-center py-3">
        <p class="mb-0">&copy; <strong> To-Do Plan. All Rights Reserved.</strong></p>
    </footer>

</body>
</html>

<script src="js/script.js"></script>