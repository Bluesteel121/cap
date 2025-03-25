<?php
session_start();
include "connect.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($password === $row["password"]) { // No password hashing
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;
            header("Location: index.php");
            exit();
        } else {
            echo "Invalid password";
        }
    } else {
        echo "Invalid username or role";
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

        <button onclick="showSection('developers')" class="bg-green-500 text-white w-full py-2 mt-4 rounded-lg hover:bg-green-700">Developers</button>
        <button onclick="showSection('staff')" class="bg-green-500 text-white w-full py-2 mt-2 rounded-lg hover:bg-green-700">Staff</button>
    </div>

    <div id="developers-container" class="bg-white p-8 rounded-lg shadow-lg text-center w-96 hidden">
        <img src="Images/logo.png" alt="Logo" class="mx-auto h-16">
        <h2 class="text-2xl font-bold mt-4">Developers</h2>
        <p class="text-gray-500 mt-2">Admin</p>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" class="border w-full px-4 py-2 rounded-lg" required>
            <input type="password" name="password" placeholder="Password" class="border w-full px-4 py-2 rounded-lg" required>
            <input type="hidden" name="role" value="developer">
            <button type="submit" class="bg-green-500 text-white w-full py-2 rounded-lg hover:bg-green-700">Login</button>
        </form>
    </div>

    <div id="staff-container" class="bg-white p-8 rounded-lg shadow-lg text-center w-96 hidden">
        <img src="Images/logo.png" alt="Logo" class="mx-auto h-16">
        <h2 class="text-2xl font-bold mt-4">Staff</h2>
        <p class="text-gray-500 mt-2">Admin</p>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" class="border w-full px-4 py-2 rounded-lg" required>
            <input type="password" name="password" placeholder="Password" class="border w-full px-4 py-2 rounded-lg" required>
            <input type="hidden" name="role" value="staff">
            <button type="submit" class="bg-green-500 text-white w-full py-2 rounded-lg hover:bg-green-700">Login</button>
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

<?php ob_end_flush(); ?>
