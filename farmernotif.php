<?php
// Include the shared database connection and functions
require_once 'db_connect.php';

// Get farmer data using the shared function
$farmer_data = getFarmerData($conn);
$login_identifier = isset($_SESSION['username']) ? $_SESSION['username'] : 
                   (isset($_SESSION['email']) ? $_SESSION['email'] : 
                   (isset($_SESSION['contact_num']) ? $_SESSION['contact_num'] : ""));

// Get the login field name
$login_field = "";
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    $login_field = "username";
} else if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
    $login_field = "email";
} else if (isset($_SESSION['contact_num']) && !empty($_SESSION['contact_num'])) {
    $login_field = "contact_num";
}

// Include GitHub upload function if you have it
if (file_exists('github_upload.php')) {
    require_once 'github_upload.php';
}

// Get the profile image source
$profileImageSrc = displayProfileImage($farmer_data['profile_picture']);




// Check for messages from redirects
if (isset($_GET['message'])) {
    $update_message = $_GET['message'];
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Profile - Pineapple Crops</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function openLogoutModal() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }
        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }
        function confirmLogout() {
            window.location.href = 'account.php'; // Change this to your logout URL
        }
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('image-preview');
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</head>
<body class="bg-green-50 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-1/4 bg-[#115D5B] p-6 h-screen fixed top-0 left-0 flex flex-col justify-between text-white">
            <div>
                <div class="flex flex-col items-center text-center">
                    <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile" class="w-20 h-20 rounded-full border mb-2 object-cover bg-white">
                    <h2 class="font-bold"><?php echo htmlspecialchars($farmer_data['name']); ?></h2>
                    <p class="text-sm"><?php echo htmlspecialchars($farmer_data['contact_num']); ?></p>
                    <p class="text-sm italic">Farmer</p>
                    <?php if(isset($farmer_data['status'])): ?>
                        <p class="text-xs mt-1 px-2 py-1 rounded-full <?= $farmer_data['status'] == 'Active' ? 'bg-green-600' : 'bg-red-600' ?>">
                            <?= htmlspecialchars($farmer_data['status']) ?>
                        </p>
                    <?php endif; ?>
                </div>
                <nav class="mt-6">
                    <ul class="space-y-2">
                        <li><a href="farmerpage.php" class="flex items-center  p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Home</a></li>
                            
                        <li><a href="farmerprofile.php" class="flex items-center  p-2 bg-[#CAEED5] text-green-700 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile</a></li>
                        <li><a href="#" class="flex items-center  p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            Notifications</a></li>
                        <li><a href="#" class="flex items-center  p-2 text-red-500 hover:text-red-700" onclick="openLogoutModal()">
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
        <main class="w-3/4 p-6 ml-[25%]">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-green-800">Farmer Profile</h1>
                <a href="farmerpage.php" class="bg-blue-600 text-white px-4 py-2 rounded">Back to Dashboard</a>
            </header>
            
            <?php if (!empty($update_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $update_message; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($update_error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $update_error; ?></span>
                </div>
            <?php endif; ?>
            
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="space-y-6">
                    <!-- Profile Picture -->
                    <div class="flex flex-col items-center mb-6">
                        <h3 class="text-lg font-bold mb-2 text-green-800">Profile Picture</h3>
                        <img id="image-preview" src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile Preview" class="w-32 h-32 rounded-full object-cover border-2 border-green-700 mb-4 bg-white">
                        
                        <input type="file" name="profile_pic" id="profile_pic" accept="image/*" 
                               class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 
                                      file:rounded-full file:border-0 file:text-sm file:font-semibold
                                      file:bg-green-100 file:text-green-700 hover:file:bg-green-200" 
                               onchange="previewImage(event)">
                    </div>
                    
                    
    
    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4">Confirm Logout</h3>
            <p class="mb-6">Are you sure you want to logout from your account?</p>
            <div class="flex justify-end space-x-4">
                <button onclick="closeLogoutModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-colors">Cancel</button>
                <button onclick="confirmLogout()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">Logout</button>
            </div>
        </div>
    </div>
</body>
</html>