<?php
// Include the centralized database connection file
require_once('db_connect.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['username']) && !isset($_SESSION['email']) && !isset($_SESSION['contact_num'])) {
    header("Location: account.php");
    exit();
}

// We'll use the existing client_acc table to get user data
$login_identifier = isset($_SESSION['username']) ? $_SESSION['username'] : 
                   (isset($_SESSION['email']) ? $_SESSION['email'] : 
                   (isset($_SESSION['contact_num']) ? $_SESSION['contact_num'] : null));

if ($login_identifier === null) {
    // No valid login credentials
    header("Location: account.php");
    exit();
}

// Determine which field to use for the query
$field = isset($_SESSION['username']) ? "username" : 
        (isset($_SESSION['email']) ? "email" : "contact_num");

// Query the client_acc table
$sql = "SELECT * FROM client_acc WHERE $field = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login_identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $full_name = $user_data['full_name'] ?? "Client User";
    $email = $user_data['email'] ?? "No email provided";
    $profile_pic = $user_data['profile_pic'] ?? null;
    $contact_num = $user_data['contact_num'] ?? null;
    $user_type = "Client";
    $status = $user_data['status'] ?? "Active";
    
    // Store client_id in session for future use
    if (isset($user_data['client_id'])) {
        $_SESSION['client_id'] = $user_data['client_id'];
    }
} else {
    // Default values if no data is found
    $full_name = "Client User";
    $email = $login_identifier;
    $profile_pic = null;
    $contact_num = null;
    $user_type = "Client";
    $status = "Active";
}
$stmt->close();

// Use the profile image display function from db_connect.php
$profileImageSrc = displayProfileImage($profile_pic);

// Fetch all harvest data for the current month and later months
$currentMonth = date('F');
$months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
$filteredMonths = array_slice($months, array_search($currentMonth, $months));

$placeholders = rtrim(str_repeat('?,', count($filteredMonths)), ',');
$sql = "SELECT farmer_name, month_of_harvest, possible_harvest, quantity, location, status 
        FROM harvests 
        WHERE month_of_harvest IN ($placeholders)
        ORDER BY FIELD(month_of_harvest, " . implode(',', array_fill(0, count($filteredMonths), '?')) . ")
        LIMIT 100";

$stmt = $conn->prepare($sql);
$params = array_merge($filteredMonths, $filteredMonths);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$harvest_result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pineapple Crops Price</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
        // Global variable to store the currently selected product
        let selectedProduct = '';

        function openLogoutModal() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }
        
        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }
        
        function confirmLogout() {
            window.location.href = 'account.php';
        }

        function filterTable() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let rows = document.querySelectorAll('.harvest-row');
            let noResultsMessage = document.getElementById('no-results');
            let visibleCount = 0;
            
            rows.forEach(row => {
                let content = row.innerText.toLowerCase();
                let productMatch = selectedProduct === '' || row.getAttribute('data-product').toLowerCase() === selectedProduct.toLowerCase();
                let searchMatch = input === '' || content.includes(input);
                
                if (productMatch && searchMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show or hide the "no results" message
            if (visibleCount === 0) {
                let message = 'No farmers currently selling';
                if (selectedProduct) {
                    message += ' ' + selectedProduct;
                }
                message += ' matching your search criteria.';
                
                noResultsMessage.textContent = message;
                noResultsMessage.style.display = 'block';
            } else {
                noResultsMessage.style.display = 'none';
            }
        }

        function filterByProduct(product) {
            // Remove highlight from all products
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('ring-4', 'ring-yellow-400');
            });
            
            // Update the filter indicator
            const filterIndicator = document.getElementById('filter-indicator');
            
            if (selectedProduct === product) {
                // If clicking the same product, clear the filter
                selectedProduct = '';
                filterIndicator.style.display = 'none';
            } else {
                // Apply the new filter
                selectedProduct = product;
                
                // Highlight the selected product
                document.querySelector(`[data-product="${product}"]`).classList.add('ring-4', 'ring-yellow-400');
                
                // Show and update the filter indicator
                document.getElementById('filtered-product-name').textContent = product;
                filterIndicator.style.display = 'flex';
            }
            
            // Apply the filter
            filterTable();
        }

        function clearFilters() {
            // Reset the selected product
            selectedProduct = '';
            
            // Clear the search input
            document.getElementById('searchInput').value = '';
            
            // Remove highlights from all products
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('ring-4', 'ring-yellow-400');
            });
            
            // Hide the filter indicator
            document.getElementById('filter-indicator').style.display = 'none';
            
            // Reset the table
            filterTable();
        }
        
        // Initialize filters when the page loads
        window.onload = function() {
            // Hide the filter indicator on page load
            document.getElementById('filter-indicator').style.display = 'none';
        }
    </script>
    <style>
        /* Add custom cursor style for clickable rows */
        .harvest-row {
            cursor: pointer;
            transition: transform 0.1s ease;
        }
        
        .harvest-row:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="flex">
    <!-- Sidebar -->
    <aside class="w-1/4 bg-gradient-to-b from-[#115D5B] to-[#0F3D3A] p-6 h-screen fixed top-0 left-0 flex flex-col justify-between text-white shadow-xl">
        <div>
            <div class="flex flex-col items-center text-center mb-8">
                <div class="relative">
                    <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile" class="w-24 h-24 rounded-full border-4 border-[#CAEED5] mb-3 object-cover shadow-md">
                    <?php if(isset($user_data['status']) && $user_data['status'] == 'Active'): ?>
                        <span class="absolute bottom-3 right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-[#115D5B] status-active"></span>
                    <?php endif; ?>
                </div>
                <h2 class="font-bold text-lg"><?php echo htmlspecialchars($full_name); ?></h2>
                <p class="text-sm italic text-[#CAEED5]"><?php echo htmlspecialchars($user_type); ?></p>
                <?php if(isset($user_data['contact_num'])): ?>
                    <p class="text-sm mt-1"><i class="fas fa-phone-alt text-xs mr-1"></i><?php echo htmlspecialchars($user_data['contact_num']); ?></p>
                <?php endif; ?>
                <p class="text-sm"><?php echo htmlspecialchars($email); ?></p>
            </div>
            <nav class="mt-6">
                <ul class="space-y-2">
                    <li><a href="#" class="block p-2 bg-[#CAEED5] text-green-700 rounded hover:bg-gray-300">Home</a></li>
                    <li><a href="clientorder.php" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Order</a></li>
                    <li><a href="#" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Notifications</a></li>
                    <li><a href="clientprofile.php" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Profile</a></li>
                    <li><a href="#" class="block p-2 text-red-500 hover:text-red-700" onclick="openLogoutModal()">Logout</a></li>
                </ul>
            </nav>
        </div>
        <footer class="text-center text-xs">&copy; 2025 Camarines Norte Lowland Rainfed Research Station</footer>
    </aside>

    <!-- Main Content -->
    <main class="w-3/4 p-6 bg-white ml-[25%]">
        <header class="flex justify-between items-center mb-6 bg-white p-4 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold text-green-700">Pineapple Crops Price</h1>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition-all">Place Order</button>
        </header>

        <!-- Product Cards - Now clickable -->
        <div class="grid grid-cols-3 gap-6 text-white font-bold mb-6">
            <div class="product-card bg-gradient-to-br from-[#115D5B] to-[#0F3D3A] p-4 rounded-lg flex items-center cursor-pointer transition-transform transform hover:scale-105 shadow-lg" 
                 onclick="filterByProduct('Pineapple Fruit')" data-product="Pineapple Fruit">
                <img src="Images/pineapple-fruit.jpg" alt="Pineapple Fruit" class="w-16 h-16 rounded-lg mr-4 object-cover border-2 border-[#CAEED5]">
                <div>
                    <h3>Pineapple Fruit</h3>
                    <p class="text-lg">₱50-60 Per Piece</p>
                </div>
            </div>
            <div class="product-card bg-gradient-to-br from-[#115D5B] to-[#0F3D3A] p-4 rounded-lg flex items-center cursor-pointer transition-transform transform hover:scale-105 shadow-lg" 
                 onclick="filterByProduct('Pineapple Juice')" data-product="Pineapple Juice">
                <img src="Images/pineapple-juice.jpg" alt="Pineapple Juice" class="w-16 h-16 rounded-lg mr-4 object-cover border-2 border-[#CAEED5]">
                <div>
                    <h3>Pineapple Juice</h3>
                    <p class="text-lg">₱50-60 Per Liter</p>
                </div>
            </div>
            <div class="product-card bg-gradient-to-br from-[#115D5B] to-[#0F3D3A] p-4 rounded-lg flex items-center cursor-pointer transition-transform transform hover:scale-105 shadow-lg" 
                 onclick="filterByProduct('Pineapple Fiber')" data-product="Pineapple Fiber">
                <img src="Images/pineapple-fiber2.png" alt="Pineapple Fiber" class="w-16 h-16 rounded-lg mr-4 object-cover border-2 border-[#CAEED5]">
                <div>
                    <h3>Pineapple Fiber</h3>
                    <p class="text-lg">₱50-60 Per Yard</p>
                </div>
            </div>
        </div>

        <!-- Filter indicator and clear button -->
        <div id="filter-indicator" class="flex justify-between items-center mb-4 bg-green-100 p-3 rounded-lg" style="display: none;">
            <p class="text-green-800">
                <span class="font-bold">Currently showing:</span> Farmers selling <span id="filtered-product-name"></span>
            </p>
            <button onclick="clearFilters()" class="bg-green-700 text-white px-3 py-1 rounded-lg hover:bg-green-800">
                Clear Filter
            </button>
        </div>
        
        <!-- Harvest Table -->
        <div class="bg-[#115D5B] p-6 rounded-lg border border-gray-300 overflow-y-auto">
            <div class="flex justify-center">
                <!--Search Bar -->
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by Name, Month, or Location"
                    class="bg-[#103635] w-3/4 p-3 rounded-full mb-4 text-white border-[2.5px] border-[#4CAF50] mt-4 focus:border-green-700 focus:ring-2 focus:ring-green-700 focus:outline-none text-center">
            </div>

            <div class="space-y-4 mt-10">
                <?php
                if ($harvest_result->num_rows > 0) {
                    while ($row = $harvest_result->fetch_assoc()) {
                        echo "<a href='clientorder.php?farmer=" . urlencode($row['farmer_name']) . "' class='block'>
                                <div class='harvest-row bg-[#103635] bg-opacity-50 border-[2.5px] border-[#4CAF50] p-4 rounded-lg shadow-md flex items-center justify-between hover:bg-[#4CAF50] hover:bg-opacity-80' data-product='" . htmlspecialchars($row['possible_harvest']) . "'>
                                    <div>
                                        <p class='font-bold text-white'>{$row['farmer_name']}</p>
                                        <p class='font-bold text-sm text-[#4CAF50]'>{$row['month_of_harvest']}</p>
                                    </div>
                                    <div class='text-center'>
                                        <p class='text-white'>{$row['possible_harvest']}</p>
                                        <p class='text-sm text-gray-500'>Possible Harvest</p>
                                    </div>
                                    <div class='text-center'>
                                        <p class='text-white'>{$row['quantity']} kg</p>
                                        <p class='text-sm text-gray-500'>Quantity</p>
                                    </div>
                                    <div class='text-center'>
                                        <p class='text-white'>{$row['location']}</p>
                                        <p class='text-sm text-gray-500'>Location</p>
                                    </div>
                                    <div class='text-center'>
                                        <span class='px-3 py-1 rounded-full text-white " . 
                                        ($row['status'] == 'Available' ? 'bg-green-500' : ($row['status'] == 'Sold' ? 'bg-red-500' : 'bg-yellow-500')) . "'>
                                            {$row['status']}
                                        </span>
                                    </div>
                                </div>
                              </a>";
                    }
                }
                ?>
                <!-- No results message -->
                <div id="no-results" class="p-4 text-center text-white bg-[#103635] rounded-lg shadow-md" style="display: none;">
                    No matching results found.
                </div>
                <?php if ($harvest_result->num_rows == 0): ?>
                <div class="p-4 text-center text-white bg-[#103635] rounded-lg shadow-md">
                    No harvest data available for the current month and beyond.
                </div>
                <?php endif; ?>
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