<?php
$farmer_data = getFarmerData($conn);
$farmer_id = $farmer_data['farmer_id'];

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
            window.location.href = 'account.php'; // Change this to your logout URL
        }
    </script>
</head>

    <!-- Sidebar -->
    <aside class="w-1/4 bg-[#115D5B] p-6 h-screen fixed top-0 left-0 flex flex-col justify-between text-white">
    <div>
    <div class="flex flex-col items-center text-center">
            <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
            <h2 class="font-bold"><?= htmlspecialchars($farmer_data['name'] ?? $farmer_data['username'] ?? 'Farmer') ?></h2>
            <p class="text-sm"><?= htmlspecialchars($contact_num) ?></p>
            <p class="text-sm italic">Farmer</p>
            <?php if(isset($farmer_data['status'])): ?>
                <p class="text-xs mt-1 px-2 py-1 rounded-full <?= $farmer_data['status'] == 'Active' ? 'bg-green-600' : 'bg-red-600' ?>">
                    <?= htmlspecialchars($farmer_data['status']) ?>
                </p>
            <?php endif; ?>
        </div>

        <nav class="mt-6">
            <ul class="space-y-2">
                <li><a href="#" class="flex items-center  p-2 bg-[#CAEED5] text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Home</a></li>
                
                <li><a href="farmerprofile.php" class="flex items-center  p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Profile</a></li>

                    <li><a href="farmernotif.php" class="flex items-center  p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
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
<main class="w-3/4 p-6 bg-white ml-[25%]">
        <div class="bg-[#0F3D3A] p-10 rounded-lg border border-green-700 w-full max-w-6xl mx-auto h-[615px]  overflow-y-auto">
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
    </div>
    
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

</html>
