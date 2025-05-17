<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user ID from session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Initialize variables with default values
$user_name = "Guest User";
$user_position = "Not logged in";
$profile_pic = "default_avatar.png";
$status = "Inactive";
$contact_num = "";

// Database connection
if ($user_id) {
    require_once 'config.php'; // Include your database connection file
    
    // Updated query to match the actual table structure
    $sql = "SELECT username, name, position, profile_pic, status, contact_number 
            FROM accounts 
            WHERE ID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        // Use the name field instead of username for display
        $user_name = $user_data['name'];
        $user_position = $user_data['position'];
        $profile_pic = !empty($user_data['profile_pic']) ? $user_data['profile_pic'] : "default_avatar.png";
        $status = $user_data['status'];
        $contact_num = $user_data['contact_number'];
    }
    
    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agricultural Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-1/5 bg-[#0F3D3A] min-h-screen text-white flex flex-col">
            <div class="flex flex-col items-center text-center mb-3 p-4">                 
                <div class="relative">                     
                    <img src="<?= isset($profile_pic) ? htmlspecialchars($profile_pic) : 'default_avatar.png' ?>" alt="Profile" class="w-24 h-24 rounded-full border-4 border-[#CAEED5] mb-3 object-cover shadow-md">                     
                    <?php if(isset($status) && $status == 'Active'): ?>                         
                        <span class="absolute bottom-3 right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-[#115D5B] status-active"></span>                     
                    <?php endif; ?>                 
                </div>                 
                <h2 class="font-bold text-lg"><?= htmlspecialchars($user_name) ?></h2>                 
                <p class="text-sm italic text-[#CAEED5]"><?= htmlspecialchars($user_position) ?></p>                 
                <?php if(isset($contact_num)): ?>                     
                    <p class="text-sm mt-1"><i class="fas fa-phone-alt text-xs mr-1"></i><?= htmlspecialchars($contact_num) ?></p>                 
                <?php endif; ?>             
            </div>
            
            <div class="border-t border-green-900 mt-2"></div>
            
            <nav class="mt-6 px-3 flex-grow">
                <ul class="space-y-1">
                    <li><a href="#" class="flex items-center p-2 rounded bg-[#CAEED5] text-green-800">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Home</a>
                    </li>
                    <li><a href="sucker_request.php" class="flex items-center p-2 rounded hover:bg-[#115D5B]">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Request</a>
                    </li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-[#115D5B]">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Notifications</a>
                    </li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-[#115D5B]">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profile</a>
                    </li>

                     <li><a href="#" class="flex items-center p-2 text-red-500 hover:text-red-700" onclick="openLogoutModal()">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="w-4/5 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-[#0F3D3A]">DASHBOARD</h1>
                <div class="flex space-x-4">
                    <div class="text-right">
                        <p class="text-sm">Total Farmers:</p>
                        <span class="bg-[#0F3D3A] text-white px-4 py-1 rounded inline-block w-24 text-center">3000</span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm">Total Customers:</p>
                        <span class="bg-[#0F3D3A] text-white px-4 py-1 rounded inline-block w-24 text-center">200</span>
                    </div>
                </div>
            </div>
            
            <!-- Request Table -->
            <div class="bg-[#0F3D3A] rounded-lg mb-6 overflow-hidden text-white">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-green-900">
                            <th class="py-3 px-4 text-left">Client ID</th>
                            <th class="py-3 px-4 text-left">Product/Month Needed</th>
                            <th class="py-3 px-4 text-left"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample data rows - make these clickable -->
                        <tr class="h-16 border-b border-green-900 hover:bg-[#115D5B] cursor-pointer" onclick="window.location='request_details.php?id=1'">
                            <td class="px-4">CL001</td>
                            <td class="px-4">Pineapple Suckers - July</td>
                            <td class="px-4 text-right"><span class="text-blue-300 hover:underline">View</span></td>
                        </tr>
                        <tr class="h-16 border-b border-green-900 hover:bg-[#115D5B] cursor-pointer" onclick="window.location='request_details.php?id=2'">
                            <td class="px-4">CL002</td>
                            <td class="px-4">Organic Fertilizer - August</td>
                            <td class="px-4 text-right"><span class="text-blue-300 hover:underline">View</span></td>
                        </tr>
                        <tr class="h-16 hover:bg-[#115D5B] cursor-pointer" onclick="window.location='request_details.php?id=3'">
                            <td class="px-4">CL003</td>
                            <td class="px-4">Pineapple Suckers - September</td>
                            <td class="px-4 text-right"><span class="text-blue-300 hover:underline">View</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Stats Cards - Make these clickable -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <a href="sucker_request.php" class="bg-[#0F3D3A] rounded-lg p-4 text-white hover:bg-[#115D5B] transition cursor-pointer">
                    <h3 class="text-center mb-2 text-sm border-b pb-2">Suckers Request:</h3>
                    <p class="text-center text-4xl font-bold mb-4">15</p>
                    <div class="text-center">
                        <span class="text-sm hover:underline">View More</span>
                    </div>
                </a>
                
                <a href="fertilizer_request.php" class="bg-[#0F3D3A] rounded-lg p-4 text-white hover:bg-[#115D5B] transition cursor-pointer">
                    <h3 class="text-center mb-2 text-sm border-b pb-2">Fertilizer Request:</h3>
                    <p class="text-center text-4xl font-bold mb-4">30</p>
                    <div class="text-center">
                        <span class="text-sm hover:underline">View More</span>
                    </div>
                </a>
                
                <a href="customer_request.php" class="bg-[#0F3D3A] rounded-lg p-4 text-white hover:bg-[#115D5B] transition cursor-pointer">
                    <h3 class="text-center mb-2 text-sm border-b pb-2">Customer Request:</h3>
                    <p class="text-center text-4xl font-bold mb-4">30</p>
                    <div class="text-center">
                        <span class="text-sm hover:underline">View More</span>
                    </div>
                </a>
                
                <a href="clients_served.php" class="bg-[#0F3D3A] rounded-lg p-4 text-white hover:bg-[#115D5B] transition cursor-pointer">
                    <h3 class="text-center mb-2 text-sm border-b pb-2">Clients Served:</h3>
                    <p class="text-center text-4xl font-bold mb-4">15</p>
                    <div class="text-center">
                        <span class="text-sm hover:underline">View More</span>
                    </div>
                </a>
            </div>
            
            <!-- Status and Chart -->
            <div class="flex">
                <!-- Order Status -->
                <div class="flex-1 h-80 flex flex-col">
                    <div class="flex justify-between text-gray-600 mb-4 pb-2 border-b">
                        <div class="text-center">
                            <p class="text-6xl font-light text-gray-700">1</p>
                            <p class="text-sm text-blue-400">Pending</p>
                        </div>
                        <div class="text-center">
                            <p class="text-6xl font-light text-gray-700">10</p>
                            <p class="text-sm text-green-500">Completed</p>
                        </div>
                        <div class="text-center">
                            <p class="text-6xl font-light text-gray-700">3</p>
                            <p class="text-sm text-red-400">Rejected</p>
                        </div>
                    </div>
                </div>
                
                <!-- Chart -->
                <div class="flex-1 h-80">
                    <svg viewBox="0 0 400 250" class="w-full h-full">
                        <!-- Y axis -->
                        <line x1="40" y1="30" x2="40" y2="220" stroke="#ccc" stroke-width="1" />
                        <!-- X axis -->
                        <line x1="40" y1="220" x2="380" y2="220" stroke="#ccc" stroke-width="1" />
                        
                        <!-- Chart bars -->
                        <rect x="60" y="130" width="30" height="90" fill="#4CAF50" />
                        <rect x="110" y="130" width="30" height="90" fill="#4CAF50" />
                        <rect x="160" y="110" width="30" height="110" fill="#4CAF50" />
                        <rect x="210" y="100" width="30" height="120" fill="#4CAF50" />
                        <rect x="260" y="90" width="30" height="130" fill="#4CAF50" />
                        <rect x="310" y="90" width="30" height="130" fill="#4CAF50" />
                        
                        <!-- Y axis labels -->
                        <text x="35" y="220" text-anchor="end" font-size="10">0</text>
                        <text x="35" y="180" text-anchor="end" font-size="10">20</text>
                        <text x="35" y="140" text-anchor="end" font-size="10">40</text>
                        <text x="35" y="100" text-anchor="end" font-size="10">60</text>
                        <text x="35" y="60" text-anchor="end" font-size="10">80</text>
                        
                        <!-- Y axis title -->
                        <text x="10" y="125" text-anchor="middle" font-size="10" transform="rotate(-90, 10, 125)">Pineapple Price per kilogram in the Philippines pesos</text>
                    </svg>
                </div>
            </div>
        </main>
    </div>

    <!-- Logout Modal -->
    <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center w-80">
            <h2 class="text-lg font-bold mb-4">Confirm Logout</h2>
            <p class="mb-6">Are you sure you want to logout?</p>
            <div class="flex justify-center gap-4">
                <button onclick="window.location.href='account.php'" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded transition">Yes, Logout</button>
                <button onclick="closeLogoutModal()" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded transition">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Logout Modal Functions
        function openLogoutModal() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }

        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }

        function confirmLogout() {
            // Here you would typically redirect to a logout endpoint
            // For now, we'll just close the modal and show an alert
            closeLogoutModal();
            alert('You have been logged out successfully.');
            // window.location.href = '/logout'; // Uncomment this to actually log out
        }

        // Close modal when clicking outside of it
        document.getElementById('logout-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });
    </script>
</body>
</html>