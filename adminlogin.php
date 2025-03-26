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
        
        $all_users_query = "SELECT * FROM accounts";
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
            $_SESSION['login_error'] = "Invalid username or role";
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
    <title>Login - CNLRRS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Font Awesome for eye icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-green-500 flex justify-center items-center h-screen">

    <a href="account.php" class="absolute top-4 left-4 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg shadow-md hover:bg-gray-300">← Back</a>

    <div id="main-container" class="bg-white p-8 rounded-lg shadow-lg text-center w-96">
        <img src="Images/logo.png" alt="Logo" class="mx-auto h-16">
        <h2 class="text-2xl font-bold mt-4">ADMINISTRATOR</h2>
        <p class="text-gray-500 mt-2">Admin</p>

        <?php
        // Display login errors if any
        if (isset($_SESSION['login_error'])) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
            echo htmlspecialchars($_SESSION['login_error']);
            echo '</div>';
            // Clear the error after displaying
            unset($_SESSION['login_error']);
        }
        ?>

        <button onclick="showSection('developers')" class="bg-green-500 text-white w-full py-2 mt-4 rounded-lg hover:bg-green-700">Developers</button>
        <button onclick="showSection('staff')" class="bg-green-500 text-white w-full py-2 mt-2 rounded-lg hover:bg-green-700">Staff</button>
    </div>

    <div id="developers-container" class="bg-white p-8 rounded-lg shadow-lg text-center w-96 hidden">
        <img src="Images/logo.png" alt="Logo" class="mx-auto h-16">
        <h2 class="text-2xl font-bold mt-4">Developers</h2>
        <p class="text-gray-500 mt-2">Admin</p>

        <form method="POST" action="adminlogin.php">
            <input type="text" name="username" placeholder="Username" class="border w-full px-4 py-2 rounded-lg mt-2" required>
            
            <div class="relative mt-2">
                <input type="password" name="password" id="devPassword" placeholder="Password" class="border w-full px-4 py-2 rounded-lg pr-10" required>
                <button type="button" onclick="togglePassword('devPassword', 'devToggleIcon')" class="absolute right-3 top-3 text-gray-500">
                    <i class="far fa-eye" id="devToggleIcon"></i>
                </button>
            </div>
            
            <input type="hidden" name="role" value="dev">
            <button type="submit" class="bg-green-500 text-white w-full py-2 mt-4 rounded-lg hover:bg-green-700">Login</button>
        </form>
    </div>

    <div id="staff-container" class="bg-white p-8 rounded-lg shadow-lg text-center w-96 hidden">
        <img src="Images/logo.png" alt="Logo" class="mx-auto h-16">
        <h2 class="text-2xl font-bold mt-4">Staff</h2>
        <p class="text-gray-500 mt-2">Admin</p>

        <form method="POST" action="adminlogin.php">
            <input type="text" name="username" placeholder="Username" class="border w-full px-4 py-2 rounded-lg mt-2" required>
            
            <div class="relative mt-2">
                <input type="password" name="password" id="staffPassword" placeholder="Password" class="border w-full px-4 py-2 rounded-lg pr-10" required>
                <button type="button" onclick="togglePassword('staffPassword', 'staffToggleIcon')" class="absolute right-3 top-3 text-gray-500">
                    <i class="far fa-eye" id="staffToggleIcon"></i>
                </button>
            </div>
            
            <input type="hidden" name="role" value="staff">
            <button type="submit" class="bg-green-500 text-white w-full py-2 mt-4 rounded-lg hover:bg-green-700">Login</button>
        </form>
    </div>

    <script>
    function showSection(section) {
        document.getElementById("main-container").classList.add("hidden");
        document.getElementById("developers-container").classList.add("hidden");
        document.getElementById("staff-container").classList.add("hidden");

        if (section === "developers") {
            document.getElementById("developers-container").classList.remove("hidden");
        } else if (section === "staff") {
            document.getElementById("staff-container").classList.remove("hidden");
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