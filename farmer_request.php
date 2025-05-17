<?php
// Include the shared database connection and functions
require_once 'db_connect.php';

// Get farmer data using the shared function
$farmer_data = getFarmerData($conn);
$farmer_id = $farmer_data['farmer_id'];

// Set default values for profile picture and contact number
$profile_pic = $farmer_data['profile_pic'] ?? '/api/placeholder/50/50';
$contact_num = $farmer_data['contact_num'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fertilizer Request | Agricultural Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-full md:w-64 lg:w-1/4 bg-[#115D5B] p-6 h-screen fixed top-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col justify-between text-white z-40">
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
                <div class="border-t border-green-900 mt-2"></div>
                
                <nav class="mt-6 px-3 flex-grow">
                    <ul class="space-y-1">
                        <li><a href="farmerpage.php" class="flex items-center p-2 rounded hover:bg-[#115D5B]">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Home</a>
                        </li>

                        <li><a href="farmerprofile.php" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            Profile</a>
                        </li>

                        <li><a href="farmer_request.php" class="flex items-center p-2 rounded bg-[#CAEED5] text-green-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Request</a>
                        </li>

                        <li><a href="farmernotif.php" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Notifications</a>
                        </li>
                        
                        <li><a href="#" class="flex items-center p-2 text-red-500 hover:text-red-700" onclick="openLogoutModal()">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        
        <!-- Main Content Area -->
     <main class="w-full md:w-[calc(100%-256px)] lg:w-3/4 bg-white md:ml-64 lg:ml-[25%] p-4 md:p-6 transition-all duration-300 ease-in-out">
            <div class="bg-white rounded-lg shadow p-6">
                <h1 class="text-2xl font-bold text-[#0F3D3A] mb-6">Fertilizer Request Form</h1>
                
                <form id="fertilizerForm" action="process_request.php" method="POST" class="space-y-6">
                    <input type="hidden" name="farmer_id" value="<?= htmlspecialchars($farmer_id) ?>">
                    
                    <!-- Fertilizer Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fertilizer Type</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="fertilizer_type" value="Ammonium Phosphate" class="h-5 w-5 text-green-600" required>
                                <span class="ml-3 block text-sm font-medium text-gray-700">
                                    Ammonium Phosphate
                                </span>
                            </label>
                            
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="fertilizer_type" value="Muriate Potash" class="h-5 w-5 text-green-600">
                                <span class="ml-3 block text-sm font-medium text-gray-700">
                                    Muriate Potash
                                </span>
                            </label>
                            
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="fertilizer_type" value="Urea" class="h-5 w-5 text-green-600">
                                <span class="ml-3 block text-sm font-medium text-gray-700">
                                    Urea
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Land Size -->
                    <div>
                        <label for="land_size" class="block text-sm font-medium text-gray-700">Land Size (hectares)</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number" name="land_size" id="land_size" step="0.1" min="0.1" 
                                   class="focus:ring-green-500 focus:border-green-500 block w-full pl-3 pr-12 py-2 sm:text-sm border-gray-300 rounded-md" 
                                   placeholder="1.5" required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">ha</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Number of Flowered Plants -->
                    <div>
                        <label for="flowered_plants" class="block text-sm font-medium text-gray-700">Number of Flowered Plants</label>
                        <input type="number" name="flowered_plants" id="flowered_plants" min="0"
                               class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                    </div>
                    
                    <!-- Sucker Age -->
                    <div>
                        <label for="sucker_age" class="block text-sm font-medium text-gray-700">Age of Suckers (months)</label>
                        <select id="sucker_age" name="sucker_age" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md" required>
                            <option value="" disabled selected>Select age range</option>
                            <option value="0-3">0-3 months</option>
                            <option value="3-6">3-6 months</option>
                            <option value="6-12">6-12 months</option>
                            <option value="12+">12+ months</option>
                        </select>
                    </div>
                    
                    <!-- Additional Notes -->
                    <div>
                        <label for="additional_notes" class="block text-sm font-medium text-gray-700">Additional Information</label>
                        <textarea id="additional_notes" name="additional_notes" rows="3" 
                                  class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border border-gray-300 rounded-md" 
                                  placeholder="Any special requirements or notes"></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#0F3D3A] hover:bg-[#115D5B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Submit Request
                        </button>
                    </div>
                </form>
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
        // Form Submission
        document.getElementById('fertilizerForm').addEventListener('submit', function(e) {
            // Form will be submitted to process_request.php
            // You can add additional validation here if needed
        });

        // Logout Modal Functions
        function openLogoutModal() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }

        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }

        document.getElementById('logout-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });

        // Mobile menu toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }
    </script>
</body>
</html>