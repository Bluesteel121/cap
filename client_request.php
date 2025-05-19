<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucker Request | Agricultural Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0F3D3A',
                        secondary: '#CAEED5',
                        accent: '#115D5B',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
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
                    
                    <!-- Added System Logs menu item -->
                    <li><a href="log.php" class="flex items-center p-2 rounded hover:bg-[#115D5B]">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        System Logs</a>
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
                <h1 class="text-2xl font-bold text-primary">Customer Request:</h1>
                <button id="request-btn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Request Suckers</span>
                </button>
            </div>
            
            <!-- Status Tabs -->
            <div class="flex space-x-2 mb-4">
                <button id="all-tab" class="px-4 py-2 rounded-md bg-green-500 text-white font-medium">All Request</button>
                <button id="pending-tab" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 font-medium">Pending</button>
                <button id="rejected-tab" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 font-medium">Rejected</button>
                <button id="complete-tab" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 font-medium">Complete</button>
            </div>
            
            <!-- Search and Filter -->
            <div class="flex space-x-4 mb-4">
                <div class="relative flex-grow">
                    <input type="text" placeholder="Search" class="w-full pl-10 pr-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <div class="absolute left-3 top-2.5">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <button class="px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 flex items-center">
                    <span>Filter</span>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Request Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-green-100 text-green-800">
                        <tr>
                            <th class="w-8 py-3 px-4 text-left">
                                <input type="checkbox" class="rounded text-green-500 focus:ring-green-500">
                            </th>
                            <th class="py-3 px-4 text-left">ID</th>
                            <th class="py-3 px-4 text-left">Name</th>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-left">Quantity</th>
                            <th class="py-3 px-4 text-left">Date ↑</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">View Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4"><input type="checkbox" class="rounded text-green-500 focus:ring-green-500"></td>
                            <td class="py-3 px-4">0001</td>
                            <td class="py-3 px-4">Andrium, Kyle P.</td>
                            <td class="py-3 px-4">Queen Pineapple</td>
                            <td class="py-3 px-4">30K Sucker</td>
                            <td class="py-3 px-4">18 Apr 2024</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">PENDING</span></td>
                            <td class="py-3 px-4"><button class="text-green-600 hover:text-green-800 font-medium">View</button></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4"><input type="checkbox" class="rounded text-green-500 focus:ring-green-500"></td>
                            <td class="py-3 px-4">0002</td>
                            <td class="py-3 px-4">Bella, John Albert A.</td>
                            <td class="py-3 px-4">Queen Pineapple</td>
                            <td class="py-3 px-4">20K Sucker</td>
                            <td class="py-3 px-4">18 Apr 2024</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">COMPLETE</span></td>
                            <td class="py-3 px-4"><button class="text-green-600 hover:text-green-800 font-medium">View</button></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4"><input type="checkbox" class="rounded text-green-500 focus:ring-green-500"></td>
                            <td class="py-3 px-4">0003</td>
                            <td class="py-3 px-4">Cossier, Fateem B.</td>
                            <td class="py-3 px-4">Queen Pineapple</td>
                            <td class="py-3 px-4">40K Sucker</td>
                            <td class="py-3 px-4">18 Apr 2024</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">REJECTED</span></td>
                            <td class="py-3 px-4"><button class="text-green-600 hover:text-green-800 font-medium">View</button></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4"><input type="checkbox" class="rounded text-green-500 focus:ring-green-500"></td>
                            <td class="py-3 px-4">0004</td>
                            <td class="py-3 px-4">Dixon, Dave C.</td>
                            <td class="py-3 px-4">Queen Pineapple</td>
                            <td class="py-3 px-4">45K Sucker</td>
                            <td class="py-3 px-4">18 Apr 2024</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">PENDING</span></td>
                            <td class="py-3 px-4"><button class="text-green-600 hover:text-green-800 font-medium">View</button></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4"><input type="checkbox" class="rounded text-green-500 focus:ring-green-500"></td>
                            <td class="py-3 px-4">0005</td>
                            <td class="py-3 px-4">Eshano, Rae A.</td>
                            <td class="py-3 px-4">Queen Pineapple</td>
                            <td class="py-3 px-4">15K Sucker</td>
                            <td class="py-3 px-4">10 Apr 2024</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">COMPLETE</span></td>
                            <td class="py-3 px-4"><button class="text-green-600 hover:text-green-800 font-medium">View</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Logout Modal -->
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
        // Tab switching functionality
        const tabs = ['all-tab', 'pending-tab', 'rejected-tab', 'complete-tab'];
        
        tabs.forEach(tabId => {
            document.getElementById(tabId).addEventListener('click', function() {
                // Reset all tabs
                tabs.forEach(id => {
                    document.getElementById(id).classList.remove('bg-green-500', 'text-white');
                    document.getElementById(id).classList.add('bg-gray-200');
                });
                
                // Activate current tab
                this.classList.remove('bg-gray-200');
                this.classList.add('bg-green-500', 'text-white');
            });
        });
        
        // Logout Modal Functions
        function openLogoutModal() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }

        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }

        function confirmLogout() {
            closeLogoutModal();
            alert('You have been logged out successfully.');
            // In a real app, redirect to login page
        }

        document.getElementById('logout-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });
    </script>
</body>
</html>