<?php
// Clear any output buffering
ob_clean();

// Start session at the very top
session_start();

// Include database connection
include "connect.php"; 

// Redirect function to minimize errors
function safeRedirect($url) {
    // Clear any existing output
    ob_clean();
    
    // Redirect
    header("Location: $url");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use null coalescing operator with default empty strings
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM accounts WHERE username = ? AND role = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Simple password check (as you mentioned security will be added later)
        if ($password === $row["password"]) {
            // Store user info in session
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;
            
            // Redirect to index or a dashboard
            safeRedirect("index.php");
        } else {
            // Store error in session for display
            $_SESSION['login_error'] = "Invalid password";
            safeRedirect("login.php");
        }
    } else {
        // Store error in session for display
        $_SESSION['login_error'] = "Invalid username or role";
        safeRedirect("login.php");
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

        <form method="POST">
            <input type="text" name="username" placeholder="Username" class="border w-full px-4 py-2 rounded-lg mt-2" required>
            <input type="password" name="password" placeholder="Password" class="border w-full px-4 py-2 rounded-lg mt-2" required>
            <input type="hidden" name="role" value="developer">
            <button type="submit" class="bg-green-500 text-white w-full py-2 mt-4 rounded-lg hover:bg-green-700">Login</button>
        </form>
    </div>

    <div id="staff-container" class="bg-white p-8 rounded-lg shadow-lg text-center w-96 hidden">
        <img src="Images/logo.png" alt="Logo" class="mx-auto h-16">
        <h2 class="text-2xl font-bold mt-4">Staff</h2>
        <p class="text-gray-500 mt-2">Admin</p>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" class="border w-full px-4 py-2 rounded-lg mt-2" required>
            <input type="password" name="password" placeholder="Password" class="border w-full px-4 py-2 rounded-lg mt-2" required>
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
    </script>
</body>
</html>