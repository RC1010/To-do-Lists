//eye-icon
function togglePassword(inputId) {
    var input = document.getElementById(inputId);
    var icon = input.nextElementSibling;
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bx-hide");
        icon.classList.add("bx-show");
    } else {
        input.type = "password";
        icon.classList.remove("bx-show");
        icon.classList.add("bx-hide");
    }
}

function toggleForm() {
    let loginForm = document.getElementById("loginForm");
    let signupForm = document.getElementById("signupForm");
    
    if (loginForm.classList.contains("d-none")) {
        loginForm.classList.remove("d-none");
        signupForm.classList.add("d-none");
        document.getElementById("login_email").focus();
    } else {
        signupForm.classList.remove("d-none");
        loginForm.classList.add("d-none");
        document.getElementById("first_name").focus();
    }
}

// For Password Handling
const signupPassword = document.getElementById("signup-password");
const confirmPassword = document.getElementById("confirm-password");
const errorMessage = document.getElementById("password-error");
const submitBtn = document.getElementById("submitBtn");

function validatePassword() {
    if (confirmPassword.value !== "") { // Check if the user has started typing in the Confirm Password field
        if (signupPassword.value !== confirmPassword.value) {
            errorMessage.style.display = "block";
            submitBtn.disabled = true; // Disable submit button if passwords don't match
        } else {
            errorMessage.style.display = "none";
            submitBtn.disabled = false;  // Enable submit button if passwords match
        }
    }
}

// Attach event listeners
signupPassword.addEventListener("input", validatePassword);
confirmPassword.addEventListener("input", validatePassword);

// AJAX Page Loading Function
function loadPage(page) {
    fetch(page) // Fetch the requested page (e.g., "profile.php")
    .then(response => response.text()) // Convert response to text (HTML content)
    .then(data => document.getElementById('content').innerHTML = data) // Inject into the page
    .catch(error => console.error('Error:', error)); // Handle errors
}