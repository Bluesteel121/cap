<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucker Request | Agricultural Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar (same as dashboard) -->
        <aside class="w-1/5 bg-[#0F3D3A] min-h-screen text-white flex flex-col">
            <div class="flex p-4 items-center space-x-3">
                <img src="/api/placeholder/50/50" alt="Profile" class="rounded-full w-12 h-12 border-2 border-green-300">
                <div>
                    <h2 class="font-bold">Ricardo Dela Cruz</h2>
                    <p class="text-xs text-gray-300">pjpcnyg@gmail.com</p>
                    <p class="text-xs italic">Farmer</p>
                </div>
            </div>
            
            <div class="border-t border-green-900 mt-2"></div>
            
            <nav class="mt-6 px-3 flex-grow">
                <ul class="space-y-1">
                    <li><a href="adminpage.php" class="flex items-center p-2 rounded hover:bg-[#115D5B]">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Home</a>
                    </li>
                    <li><a href="#" class="flex items-center p-2 rounded bg-[#CAEED5] text-green-800">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
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
                        Logout</a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="w-4/5 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-[#0F3D3A]">SUCKER REQUEST</h1>
                <div class="flex space-x-4">
                    <div class="text-right">
                        <p class="text-sm">Total Requests:</p>
                        <span class="bg-[#0F3D3A] text-white px-4 py-1 rounded inline-block w-24 text-center">5</span>
                    </div>
                </div>
            </div>
            
            <!-- Status Tabs -->
            <div class="flex space-x-4 mb-6">
                <button class="px-4 py-2 rounded-t-lg bg-[#0F3D3A] text-white">All Request</button>
                <button class="px-4 py-2 rounded-t-lg bg-gray-300 hover:bg-gray-400">Pending</button>
                <button class="px-4 py-2 rounded-t-lg bg-gray-300 hover:bg-gray-400">Rejected</button>
                <button class="px-4 py-2 rounded-t-lg bg-gray-300 hover:bg-gray-400">Complete</button>
            </div>
            
            <!-- Request Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-[#0F3D3A] text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">LID</th>
                            <th class="py-3 px-4 text-left">Name</th>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-left">Quantity</th>
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">View Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="py-3 px-4">0003</td>
                            <td class="py-3 px-4">Andrum, Kyle R.</td>
                            <td class="py-3 px-4">Quorni Pineapple</td>
                            <td class="py-3 px-4">2016 Sucker</td>
                            <td class="py-3 px-4">9 July 2014</td>
                            <td class="py-3 px-4"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">APROVED</span></td>
                            <td class="py-3 px-4"><button class="text-blue-500 hover:underline">View</button></td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4">0002</td>
                            <td class="py-3 px-4">Bella, John Blount A.</td>
                            <td class="py-3 px-4">Quorni Pineapple</td>
                            <td class="py-3 px-4">2016 Sucker</td>
                            <td class="py-3 px-4">9 July 2014</td>
                            <td class="py-3 px-4"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">APROVED</span></td>
                            <td class="py-3 px-4"><button class="text-blue-500 hover:underline">View</button></td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4">0003</td>
                            <td class="py-3 px-4">Cossier, Fatemie B.</td>
                            <td class="py-3 px-4">Quorni Pineapple</td>
                            <td class="py-3 px-4">405 Sucker</td>
                            <td class="py-3 px-4">9 July 2014</td>
                            <td class="py-3 px-4"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">APROVED</span></td>
                            <td class="py-3 px-4"><button class="text-blue-500 hover:underline">View</button></td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4">0004</td>
                            <td class="py-3 px-4">Dixon, Dave C.</td>
                            <td class="py-3 px-4">Quorni Pineapple</td>
                            <td class="py-3 px-4">415 Sucker</td>
                            <td class="py-3 px-4">9 July 2014</td>
                            <td class="py-3 px-4"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">APROVED</span></td>
                            <td class="py-3 px-4"><button class="text-blue-500 hover:underline">View</button></td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4">0005</td>
                            <td class="py-3 px-4">Eshima, Bin A.</td>
                            <td class="py-3 px-4">Quorni Pineapple</td>
                            <td class="py-3 px-4">115 Sucker</td>
                            <td class="py-3 px-4">9 July 2004</td>
                            <td class="py-3 px-4"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">APROVED</span></td>
                            <td class="py-3 px-4"><button class="text-blue-500 hover:underline">View</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Logout Modal (same as dashboard) -->
    <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center w-80">
            <h2 class="text-lg font-bold mb-4">Confirm Logout</h2>
            <p class="mb-6">Are you sure you want to logout?</p>
            <div class="flex justify-center gap-4">
                <button onclick="confirmLogout()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded transition">Yes, Logout</button>
                <button onclick="closeLogoutModal()" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded transition">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Logout Modal Functions (same as dashboard)
        function openLogoutModal() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }

        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }

        function confirmLogout() {
            closeLogoutModal();
            alert('You have been logged out successfully.');
            // window.location.href = '/logout';
        }

        document.getElementById('logout-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });
    </script>
</body>
</html>