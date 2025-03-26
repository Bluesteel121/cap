<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

function debugLog($message) {
    error_log($message);
    file_put_contents('login_debug.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

include "connect.php"; 

// Login Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    debugLog("Attempting farmer login - Email: $email");

    try {
        // Use prepared statement for login
        $stmt = $conn->prepare("SELECT * FROM farmer_acc WHERE email = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // Verify password (use password_verify if using password_hash)
            if ($password === $row['password']) {
                // Create session with user's email
                $_SESSION["email"] = $email;
                
                debugLog("Login successful for: $email");
                
                // Ensure no output before header redirect
                header("Location: farmerpage.php");
                exit();
            } else {
                debugLog("Login failed for email: $email - Invalid password");
                $_SESSION['login_error'] = "Invalid email or password";
                header("Location: farmerlogin.php");
                exit();
            }
        } else {
            debugLog("Login failed for email: $email - Email not found");
            $_SESSION['login_error'] = "Invalid email or password";
            header("Location: farmerlogin.php");
            exit();
        }
    } catch (Exception $e) {
        debugLog("Exception: " . $e->getMessage());
        $_SESSION['login_error'] = "An error occurred during login";
        header("Location: farmerlogin.php");
        exit();
    }
}

// Registration Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'register') {
    $email = $_POST["email"] ?? "";
    $contact = $_POST["contact"] ?? "";
    $password = $_POST["password"] ?? "";

    debugLog("Attempting farmer registration - Email: $email");

    try {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT * FROM farmer_acc WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            debugLog("Registration failed - Email already exists: $email");
            $_SESSION['registration_error'] = "Email already exists";
            header("Location: farmerlogin.php");
            exit();
        }

        // Prepare insert statement for farmer_acc
        $insert_stmt = $conn->prepare("INSERT INTO farmer_acc (email, contact_num, password) VALUES (?, ?, ?)");
        if ($insert_stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $insert_stmt->bind_param("sss", $email, $contact, $password);
        
        if ($insert_stmt->execute()) {
            debugLog("Registration successful for: $email");
            $_SESSION['registration_success'] = "Account created successfully. Please log in.";
            header("Location: farmerlogin.php");
            exit();
        } else {
            throw new Exception("Execute failed: " . $insert_stmt->error);
        }
    } catch (Exception $e) {
        debugLog("Registration Exception: " . $e->getMessage());
        $_SESSION['registration_error'] = "An error occurred during registration";
        header("Location: farmerlogin.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Login/Signup - CNLRRS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Font Awesome for eye icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="h-screen flex relative">
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
            <h2 class="text-2xl font-bold text-center mb-4">Farmer Login</h2>
            <form id="login-form" method="POST" action="farmerlogin.php">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" class="border w-full px-4 py-2 rounded-lg mt-2" required>
                
                <div class="relative mt-2">
                    <input type="password" name="password" id="loginPassword" placeholder="Password" class="border w-full px-4 py-2 rounded-lg pr-10" required>
                    <button type="button" onclick="togglePassword('loginPassword', 'loginToggleIcon')" class="absolute right-3 top-3 text-gray-500">
                        <i class="far fa-eye" id="loginToggleIcon"></i>
                    </button>
                </div>
                
                <button type="submit" class="bg-green-500 text-white w-full py-2 mt-4 rounded-lg hover:bg-green-700">
                    Login
                </button>
            </form>
            <p class="mt-4 text-center">
                New farmer? 
                <a href="#" onclick="showSection('signup')" class="text-green-500 hover:underline">Create Account</a>
            </p>
        </div>

        <!-- Signup Form -->
        <div id="signup-section" class="hidden">
            <h2 class="text-2xl font-bold text-center mb-4">Farmer Registration</h2>
            <form id="signup-form" method="POST" action="farmerlogin.php">
                <input type="hidden" name="action" value="register">
                <input type="email" name="email" placeholder="Email" class="w-full p-2 border rounded mb-2" required>
                <input type="tel" name="contact" placeholder="Contact Number" class="w-full p-2 border rounded mb-2" required>
                
                <div class="relative mb-2">
                    <input type="password" name="password" id="signupPassword" placeholder="Password" class="w-full p-2 border rounded pr-10" required>
                    <button type="button" onclick="togglePassword('signupPassword', 'signupToggleIcon')" class="absolute right-3 top-3 text-gray-500">
                        <i class="far fa-eye" id="signupToggleIcon"></i>
                    </button>
                </div>
                
                <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">
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