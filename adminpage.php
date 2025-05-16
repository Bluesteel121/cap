<?php
// Use a single database connection approach - session is already started in db_connect.php
require_once 'db_connect.php';

// Debug - Display all session variables to see what's available
echo "<!-- Session Debug: ";
print_r($_SESSION);
echo " -->";

// Use simpler approach - try to use the username from session if available
$username = $_SESSION['username'] ?? null;
$user_id = $_SESSION['user_id'] ?? $_SESSION['ID'] ?? null;

// Try all possible session variables that might contain user identity
$possible_username_keys = ['username', 'admin_username', 'user', 'admin_user', 'login_user'];
$possible_id_keys = ['ID', 'id', 'user_id', 'admin_id', 'userid'];

foreach ($possible_username_keys as $key) {
    if (isset($_SESSION[$key]) && !$username) {
        $username = $_SESSION[$key];
        echo "<!-- Found username in $_SESSION[$key]: $username -->";
    }
}

foreach ($possible_id_keys as $key) {
    if (isset($_SESSION[$key]) && !$user_id) {
        $user_id = $_SESSION[$key];
        echo "<!-- Found user_id in $_SESSION[$key]: $user_id -->";
    }
}

// Query the accounts table for user info
try {
    // Try to get user by username first if available (most reliable)
    if ($username) {
        echo "<!-- Trying to find user by username: $username -->";
        $query = "SELECT * FROM accounts WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
    } 
    // Then try by ID if available
    elseif ($user_id) {
        echo "<!-- Trying to find user by ID: $user_id -->";
        $query = "SELECT * FROM accounts WHERE ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
    }
    // Fallback to first account (for testing)
    else {
        echo "<!-- No session variables found, using first account -->";
        $query = "SELECT * FROM accounts LIMIT 1";
        $stmt = $conn->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user_info = $result->fetch_assoc();
        $user_name = $user_info['name'];
        $user_position = $user_info['position'];
        $user_role = $user_info['role'];
        $profile_pic = 'default-profile.jpg';
        
        echo "<!-- Found user: $user_name, Position: $user_position, Role: $user_role -->";
    } else {
        // Handle case where user is not found
        $user_name = 'Unknown User';
        $user_position = 'Staff';
        $profile_pic = 'default-profile.jpg';
        
        echo "<!-- No user found in database -->";
    }
} catch (Exception $e) {
    // Log error and set default values
    $error_message = $e->getMessage();
    error_log("Database error: " . $error_message);
    $user_name = 'Error retrieving user';
    $user_position = 'Please try again later';
    $profile_pic = 'default-profile.jpg';
    
    echo "<!-- Database error: $error_message -->";
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adminpage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Replace with actual Font Awesome kit -->
    <script src="https://kit.fontawesome.com/your_actual_kit.js" crossorigin="anonymous"></script>
    <script>
        function openLogoutModal() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }
        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }
        function confirmLogout() {
            window.location.href = 'account.php'; // Changed to proper logout URL
        }
    </script>
</head>
<body>
    <!-- Sidebar -->
    <aside class="w-1/4 bg-[#115D5B] p-6 h-screen fixed top-0 left-0 flex flex-col justify-between text-white">
        <div>
            <div class="flex flex-col items-center text-center">
                <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
                <h2 class="font-bold"><?= htmlspecialchars($user_name) ?></h2>
                <p class="text-sm italic"><?= htmlspecialchars($user_position) ?></p>
            </div>

            <nav class="mt-6">
                <ul class="space-y-2">
                    <li><a href="#" class="flex items-center p-2 bg-[#CAEED5] text-green-700 rounded">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Home</a></li>
                    
                    <li><a href="#" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Profile</a></li>

                    <li><a href="#" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Notifications</a></li>
                    <li><a href="log.php" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        System Logs</a></li>
                    <li><a href="#" class="flex items-center p-2 text-red-500 hover:text-red-700" onclick="openLogoutModal()">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout</a></li>
                </ul>
            </nav>
        </div>
        <footer class="text-center text-xs">&copy; 2025 Camarines Norte Lowland Rainfed Research Station</footer>
    </aside>
    
    <!-- Main Content -->
    <main class="w-3/4 p-6 bg-white ml-[25%]">
        <div class="bg-[#0F3D3A] p-10 rounded-lg border border-green-700 w-full max-w-6xl mx-auto h-[615px] overflow-y-auto">
            <div class="grid grid-cols-3 gap-6 text-white text-center font-bold">
                <div>
                    <h3 class="border-b pb-2">Customer Request</h3>
                    <div class="bg-gray-300 h-64 rounded-lg mt-4"></div>
                </div>
                <div>
                    <h3 class="border-b pb-2">Customer</h3>
                    <div class="bg-gray-300 h-64 rounded-lg mt-4"></div>
                </div>
                <div>
                    <h3 class="border-b pb-2">Farmer</h3>
                    <div class="bg-gray-300 h-64 rounded-lg mt-4"></div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Logout Modal -->
    <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <h2 class="text-lg font-bold">Confirm Logout</h2>
            <p class="mt-2">Are you sure you want to logout?</p>
            <div class="mt-4 flex justify-center gap-4">
                <button onclick="confirmLogout()" class="bg-red-500 text-white px-4 py-2 rounded">Yes</button>
                <button onclick="closeLogoutModal()" class="bg-gray-300 px-4 py-2 rounded">No</button>
            </div>
        </div>
    </div>
</body>
</html>