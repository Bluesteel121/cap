<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


ob_start();

session_start();


function debugLog($message) {
    error_log($message);
   
    file_put_contents('login_detailed_debug.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

include "connect.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";

    
    debugLog("Database Connection Status: " . ($conn ? "Connected" : "Failed"));
    debugLog("Attempting login - Username: $username, Role: $role");

    try {
        
        $all_users_query = "SELECT * FROM farmer_acc";
        $all_users_result = $conn->query($all_users_query);
        
        debugLog("Total users in database: " . $all_users_result->num_rows);
        
       
        while ($user = $all_users_result->fetch_assoc()) {
            debugLog("Existing User - Username: " . $user['username'] . ", Role: " . $user['role']);
        }

        
        $stmt = $conn->prepare("SELECT * FROM accounts WHERE username = ? AND role = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        debugLog("Query result rows: " . $result->num_rows);

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
           
            debugLog("Stored Password: " . $row["password"]);
            debugLog("Submitted Password: " . $password);

            if ($password === $row["password"]) {
                $_SESSION["username"] = $username;
                $_SESSION["role"] = $role;
                
                debugLog("Password match. Redirecting to index.php");
                
                ob_clean();
                header("Location: adminpage.php");
                exit();
            } else {
                debugLog("Password mismatch for user: $username");
                $_SESSION['login_error'] = "Invalid password";
                header("Location: adminlogin.php");
                exit();
            }
        } else {
            debugLog("No matching user found for Username: $username, Role: $role");
            $_SESSION['login_error'] = "Invalid account";
            header("Location: adminlogin.php");
            exit();
        }
    } catch (Exception $e) {
        debugLog("Exception: " . $e->getMessage());
        $_SESSION['login_error'] = "An error occurred during login";
        header("Location: adminlogin.php");
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
    <!-- Replace with actual Font Awesome kit -->
    <script src="https://kit.fontawesome.com/your_actual_kit.js" crossorigin="anonymous"></script>
</head>
<body class="h-screen flex relative">

    <!-- Back Button -->
    <a href="account.php" class="absolute top-4 left-4 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300">
        ← Back 
    </a>

    <!-- Login/Signup Container -->
    <div class="m-auto bg-white p-8 rounded-lg shadow-lg w-96">
        <img src="Images/logo.png" alt="Logo" class="mx-auto h-16 mb-4">
        
        <!-- Login Form -->
        <div id="login-section">
            <h2 class="text-2xl font-bold text-center mb-4">Farmer Login</h2>
            <form id="login-form" class="space-y-4">
                <input type="email" name="email" placeholder="Email" class="w-full p-2 border rounded" required>
                <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded" required>
                <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">
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
            <form id="signup-form" class="space-y-4">
                <input type="text" name="company" placeholder="Company Name" class="w-full p-2 border rounded" required>
                <input type="email" name="email" placeholder="Email" class="w-full p-2 border rounded" required>
                <input type="text" name="contact" placeholder="Contact Person" class="w-full p-2 border rounded" required>
                <input type="tel" name="phone" placeholder="Phone Number" class="w-full p-2 border rounded" required>
                <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded" required>
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

    // Add form submission handlers
    document.getElementById('login-form').addEventListener('submit', handleLogin);
    document.getElementById('signup-form').addEventListener('submit', handleSignup);

    async function handleLogin(e) {
        e.preventDefault();
        // Add login logic similar to admin login
    }

    async function handleSignup(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('register_client.php', {
                method: 'POST',
                body: formData
            });
            
            // Handle response
        } catch (error) {
            console.error('Error:', error);
        }
    }
    </script>
</body>
</html>