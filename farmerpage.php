<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmerpage</title>
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
<body class="bg-[#144D42] flex">
    <!-- Sidebar -->
    <div class="w-1/4 bg-white p-6 h-screen flex flex-col justify-between">
        <div>
            <div class="flex flex-col items-center text-center">
                <img src="profile-pic.jpg" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
                <h2 class="font-bold">Ricardo Dela Cruz</h2>
                <p class="text-sm text-gray-500">jpcn@gmail.com</p>
                <p class="text-sm italic">Farmer</p>
            </div>
            <div class="mt-6">
                <button class="w-full bg-[#115D5B] text-white py-2 px-4 rounded flex items-center gap-2">
                    &#127968; Home
                </button>
                <ul class="mt-4 space-y-2">
                    <li class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-black">
                        &#128230; Inventory
                    </li>
                    <li class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-black">
                        &#128276; Notifications
                    </li>
                    <li class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-black">
                        &#128100; Profile
                    </li>
                    <li class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-red-500" onclick="openLogoutModal()">
                        &#128682; Logout
                    </li>
                </ul>
            </div>
        </div>
        <footer class="text-center text-xs text-gray-500">
            &copy; 2025 Camarines Norte Lowland Rainfed Research Station. All Rights Reserved.
        </footer>
    </div>
    
    <!-- Main Content -->
    <div class="w-3/4 p-6">
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
</body>
</html>
