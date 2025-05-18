<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

function debugLog($message) {
    error_log($message);
     file_put_contents('/tmp/client_login.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

include "connect.php"; 

// Login Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'login') {
    $user_input = $_POST["user_input"] ?? ""; // Changed from email to user_input
    $password = $_POST["password"] ?? "";

    debugLog("Attempting login - User input: $user_input");

    try {
        // Use prepared statement for login that checks both email and username
        $stmt = $conn->prepare("SELECT * FROM client_acc WHERE email = ? OR username = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ss", $user_input, $user_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // Verify password (use password_verify if using password_hash)
            if ($password === $row['password']) {
                // Store client_ID in session
                $_SESSION["client_ID"] = $row['client_ID'];
                $_SESSION["email"] = $row['email'];
                
                debugLog("Login successful for user: $user_input");
                
                // Ensure no output before header redirect
                header("Location: clientpage.php");
                exit();
            } else {
                debugLog("Login failed for user: $user_input - Invalid password");
                $_SESSION['login_error'] = "Invalid username/email or password";
                header("Location: clientlogin.php");
                exit();
            }
        } else {
            debugLog("Login failed for user: $user_input - User not found");
            $_SESSION['login_error'] = "Invalid username/email or password";
            header("Location: clientlogin.php");
            exit();
        }
    } catch (Exception $e) {
        debugLog("Exception: " . $e->getMessage());
        $_SESSION['login_error'] = "An error occurred during login";
        header("Location: clientlogin.php");
        exit();
    }
}

// Registration Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = $_POST["username"] ?? "";
    $email = $_POST["email"] ?? "";
    $fullname = $_POST["fullname"] ?? "";
    $phone = $_POST["phone"] ?? "";
    $password = $_POST["password"] ?? "";

    debugLog("Attempting registration - Email: $email");

    try {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT * FROM client_acc WHERE email = ? OR username = ?");
        $check_stmt->bind_param("ss", $email, $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            if ($row['email'] === $email) {
                debugLog("Registration failed - Email already exists: $email");
                $_SESSION['registration_error'] = "Email already exists";
            } else {
                debugLog("Registration failed - Username already exists: $username");
                $_SESSION['registration_error'] = "Username already exists";
            }
            header("Location: clientlogin.php");
            exit();
        }

        // Prepare insert statement - Changed contact_person to full_name
        $insert_stmt = $conn->prepare("INSERT INTO client_acc (email, password, phone_number, username, full_name) VALUES (?, ?, ?, ?, ?)");
        if ($insert_stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $insert_stmt->bind_param("sssss", $email, $password, $phone, $username, $fullname);
        
        if ($insert_stmt->execute()) {
            debugLog("Registration successful for: $email");
            $_SESSION['registration_success'] = "Account created successfully. Please log in.";
            header("Location: clientlogin.php");
            exit();
        } else {
            throw new Exception("Execute failed: " . $insert_stmt->error);
        }
    } catch (Exception $e) {
        debugLog("Registration Exception: " . $e->getMessage());
        $_SESSION['registration_error'] = "An error occurred during registration";
        header("Location: clientlogin.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login/Signup - CNLRRS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Font Awesome for eye icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="h-screen flex relative bg-gray-100">
    <!-- Back Button -->
    <a href="account.php" class="absolute top-4 left-4 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300">
        ← Back 
    </a>

    <!-- Login/Signup Container -->
    <div class="m-auto bg-white p-8 rounded-lg shadow-lg w-96">
        <img src="Images/logo.png" alt="Logo" class="mx-auto h-16 mb-4">
        
        <!-- Error/Success Messages -->
        <?php
        if (isset($_SESSION['login_error'])) {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>" . 
                 htmlspecialchars($_SESSION['login_error']) . 
                 "</div>";
            unset($_SESSION['login_error']);
        }
        if (isset($_SESSION['registration_error'])) {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>" . 
                 htmlspecialchars($_SESSION['registration_error']) . 
                 "</div>";
            unset($_SESSION['registration_error']);
        }
        if (isset($_SESSION['registration_success'])) {
            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>" . 
                 htmlspecialchars($_SESSION['registration_success']) . 
                 "</div>";
            unset($_SESSION['registration_success']);
        }
        ?>
        
        <!-- Login Form -->
        <div id="login-section">
            <h2 class="text-2xl font-bold text-center mb-4">Client Login</h2>
            <form id="login-form" method="POST" action="clientlogin.php" autocomplete="off" novalidate>
                <input type="hidden" name="action" value="login">
                <div class="mb-4">
                    <label for="user_input" class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                    <input type="text" id="user_input" name="user_input" placeholder="Enter your username or email" class="border w-full px-4 py-2 rounded-lg focus:ring-green-500 focus:border-green-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="loginPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="loginPassword" placeholder="Enter your password" class="border w-full px-4 py-2 rounded-lg pr-10 focus:ring-green-500 focus:border-green-500">
                        <button type="button" onclick="togglePassword('loginPassword', 'loginToggleIcon')" class="absolute right-3 top-3 text-gray-500">
                            <i class="far fa-eye" id="loginToggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="bg-green-500 text-white w-full py-2 mt-4 rounded-lg hover:bg-green-700 transition-colors duration-200">
                    Login
                </button>
            </form>
            <p class="mt-4 text-center">
                New client? 
                <a href="#" onclick="showSection('signup')" class="text-green-500 hover:underline">Create Account</a>
            </p>
        </div>

        <!-- Signup Form -->
        <div id="signup-section" class="hidden">
            <h2 class="text-2xl font-bold text-center mb-4">Client Registration</h2>
            <form id="signup-form" method="POST" action="clientlogin.php" autocomplete="off" novalidate>
                <input type="hidden" name="action" value="register">
                
                <div class="mb-3">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" class="w-full p-2 border rounded focus:ring-green-500 focus:border-green-500" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" class="w-full p-2 border rounded focus:ring-green-500 focus:border-green-500" required>
                </div>
                
                <div class="mb-3">
                    <label for="fullname" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" class="w-full p-2 border rounded focus:ring-green-500 focus:border-green-500" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter phone number" class="w-full p-2 border rounded focus:ring-green-500 focus:border-green-500" required>
                </div>
                
                <div class="mb-3">
                    <label for="signupPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="signupPassword" placeholder="Create a password" class="w-full p-2 border rounded pr-10 focus:ring-green-500 focus:border-green-500">
                        <button type="button" onclick="togglePassword('signupPassword', 'signupToggleIcon')" class="absolute right-3 top-3 text-gray-500">
                            <i class="far fa-eye" id="signupToggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600 transition-colors duration-200">
                    Register
                </button>
            </form>
            <p class="mt-4 text-center">
                Already have an account? 
                <a href="#" onclick="showSection('login')" class="text-green-500 hover:underline">Login here</a>
            </p>
        </div>
    </div>

    <script>
    // Disable any browser validation
    document.addEventListener('DOMContentLoaded', function() {
        // Disable HTML5 validation
        document.getElementById('login-form').setAttribute('novalidate', '');
        document.getElementById('signup-form').setAttribute('novalidate', '');
        
        // Override submit behavior to prevent any validation
        document.getElementById('signup-form').addEventListener('submit', function(e) {
            // Allow form submission without any client-side validation
        });
    });

    function showSection(section) {
        const login = document.getElementById('login-section');
        const signup = document.getElementById('signup-section');
        
        if(section === 'signup') {
            login.classList.add('hidden');
            signup.classList.remove('hidden');
        } else {
            signup.classList.add('hidden');
            login.classList.remove('hidden');
        }
    }

    function togglePassword(passwordFieldId, iconId) {
        const passwordField = document.getElementById(passwordFieldId);
        const icon = document.getElementById(iconId);
        
        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordField.type = "password";
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>
</body>
</html>