<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pineapple Crops Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        function openLogoutModal() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }
        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }
        function confirmLogout() {
            window.location.href = 'account.php'; // Update your logout URL here
        }
    </script>
</head>
<body class="flex">

<?php
session_start();
$conn = new mysqli("localhost", "root", "", "capstone");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get farmer information - use session farmer_id or default to 1
$farmer_id = isset($_SESSION['farmer_id']) ? $_SESSION['farmer_id'] : 1;
$farmer_data = [];

try {
    $farmer_query = "SELECT * FROM farmer_acc WHERE farmer_id = ?";
    $stmt = $conn->prepare($farmer_query);
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $farmer_data = $row;
    } else {
        // If farmer not found, get the first farmer
        $result = $conn->query("SELECT * FROM farmer_acc LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $farmer_data = $row;
            $farmer_id = $row['farmer_id'];
        }
    }
} catch (Exception $e) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Error: " . $e->getMessage() . "</div>";
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_flowered'])) {
            $amount = isset($_POST['flowered_amount']) ? intval($_POST['flowered_amount']) : 1;
            $update_query = $conn->prepare("UPDATE farmer_acc SET flowered = flowered + ? WHERE farmer_id = ?");
            $update_query->bind_param("ii", $amount, $farmer_id);
            $update_query->execute();
            // Update local data
            $farmer_data['flowered'] = isset($farmer_data['flowered']) ? $farmer_data['flowered'] + $amount : $amount;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } elseif (isset($_POST['add_pested'])) {
            $amount = isset($_POST['pested_amount']) ? intval($_POST['pested_amount']) : 1;
            $update_query = $conn->prepare("UPDATE farmer_acc SET pested = pested + ? WHERE farmer_id = ?");
            $update_query->bind_param("ii", $amount, $farmer_id);
            $update_query->execute();
            // Update local data
            $farmer_data['pested'] = isset($farmer_data['pested']) ? $farmer_data['pested'] + $amount : $amount;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } elseif (isset($_POST['reset_flowered'])) {
            $update_query = $conn->prepare("UPDATE farmer_acc SET flowered = 0 WHERE farmer_id = ?");
            $update_query->bind_param("i", $farmer_id);
            $update_query->execute();
            // Update local data
            $farmer_data['flowered'] = 0;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } elseif (isset($_POST['reset_pested'])) {
            $update_query = $conn->prepare("UPDATE farmer_acc SET pested = 0 WHERE farmer_id = ?");
            $update_query->bind_param("i", $farmer_id);
            $update_query->execute();
            // Update local data
            $farmer_data['pested'] = 0;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    } catch (Exception $e) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Error updating stats: " . $e->getMessage() . "</div>";
    }
}

// Get stats values with fallbacks to zero if not set
$flowered = isset($farmer_data['flowered']) ? $farmer_data['flowered'] : 101;
$pested = isset($farmer_data['pested']) ? $farmer_data['pested'] : 28;
$total = $flowered + $pested;
$harvested_percent = $total > 0 ? round(($flowered / $total) * 100) : 65;
$damaged_percent = $total > 0 ? round(($pested / $total) * 100) : 35;
$total_harvest = $total; // Set total harvest to the sum of flowered and pested

// Get farmer name for display
$farmer_name = isset($farmer_data['username']) ? $farmer_data['username'] : 'Unknown Farmer';

// Sample stats for top cards
$pending_orders = 100;
$plantation_area = "300 ha";
$last_harvest = "1000 tons";

// Create sample order data if not exists
try {
    // Check if customer_orders table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'customer_orders'");
    if ($table_check->num_rows == 0) {
        // Create customer_orders table if it doesn't exist
        $create_table = "CREATE TABLE customer_orders (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            farmer_id INT(11) NOT NULL,
            customer_name VARCHAR(100) NOT NULL,
            order_date DATE NOT NULL,
            location VARCHAR(100) NOT NULL,
            order_type VARCHAR(100) NOT NULL,
            average_weight VARCHAR(50) NOT NULL,
            quantity VARCHAR(50) NOT NULL,
            price VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($create_table) === TRUE) {
            // Insert sample data for this farmer
            $sample_insert = $conn->prepare("INSERT INTO customer_orders 
                (farmer_id, customer_name, order_date, location, order_type, average_weight, quantity, price, status) VALUES 
                (?, 'Rama', '2022-02-28', 'San Lorenzo', 'Pineapple Crop', '1/kg', '10 tons', '₱50.00 / Piece', 'Requested'),
                (?, 'Rama', '2022-02-28', 'Basud', 'Pineapple Juice', '1/kg', '10 tons', '₱50.00 / Piece', 'Requested'),
                (?, 'Rama', '2022-02-28', 'Hyderabad', 'Pineapple Silk', '1/kg', '10 tons', '₱50.00 / Piece', 'Requested')");
            $sample_insert->bind_param("iii", $farmer_id, $farmer_id, $farmer_id);
            $sample_insert->execute();
        }
    }
} catch (Exception $e) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Error: " . $e->getMessage() . "</div>";
}
?>

<!-- Sidebar -->
<aside class="w-1/4 bg-[#115D5B] p-6 h-screen flex flex-col justify-between text-white">
    <div>
        <div class="flex flex-col items-center text-center">
            <?php 
            $profile_pic = !empty($farmer_data['profile_picture']) ? $farmer_data['profile_picture'] : 'profile.jpg';
            ?>
            <img src="<?= $profile_pic ?>" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
            <h2 class="font-bold"><?= $farmer_name ?></h2>
            <p class="text-sm"><?= isset($farmer_data['contact_num']) ? $farmer_data['contact_num'] : 'No Contact' ?></p>
            <p class="text-sm italic">Farmer</p>
            <?php if(isset($farmer_data['status'])): ?>
                <p class="text-xs mt-1 px-2 py-1 rounded-full <?= $farmer_data['status'] == 'Active' ? 'bg-green-600' : 'bg-red-600' ?>">
                    <?= $farmer_data['status'] ?>
                </p>
            <?php endif; ?>
        </div>
        <nav class="mt-6">
            <ul class="space-y-2">
                <li><a href="#" class="block p-2 bg-[#CAEED5] text-green-700 rounded">Home</a></li>
                <li><a href="#" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Profile</a></li>
                <li><a href="#" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Notifications</a></li>
                <li><a href="#" class="block p-2 text-red-500 hover:text-red-700" onclick="openLogoutModal()">Logout</a></li>
            </ul>
        </nav>
    </div>
    <footer class="text-center text-xs">&copy; 2025 Camarines Norte Lowland Rainfed Research Station</footer>
</aside>

<!-- Main Content -->
<main class="w-3/4 p-6 bg-white">

    <header class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-green-700">Pineapple Crops Dashboard</h1>
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Place Order</button>
    </header>

    <!-- Top Stats Cards -->
    <div class="flex gap-4 mb-6">
        <!-- Pending Orders Card -->
        <div class="flex-1 bg-[#CAEED5] p-4 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-[#0D3D3B] p-3 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold"><?= $pending_orders ?></h2>
                    <p class="text-gray-700">Pending Orders</p>
                </div>
            </div>
        </div>
        
        <!-- Area of Plantation Card -->
        <div class="flex-1 bg-[#CAEED5] p-4 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-[#0D3D3B] p-3 rounded-full">
                    <span class="text-white font-bold text-xl">A</span>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold"><?= $plantation_area ?></h2>
                    <p class="text-gray-700">Area of Plantation</p>
                </div>
            </div>
        </div>
        
        <!-- Last Harvest Card -->
        <div class="flex-1 bg-[#CAEED5] p-4 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-[#0D3D3B] p-3 rounded-full">
                    <span class="text-white font-bold text-xl">T</span>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold"><?= $last_harvest ?></h2>
                    <p class="text-gray-700">Last Harvest</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <!-- Orders Section - Left Column -->
        <div class="w-full md:w-1/2 space-y-4">
            <h2 class="text-xl font-semibold text-gray-700">Orders</h2>
            
            <?php
            // Get orders for this farmer
            try {
                $orders_query = $conn->prepare("SELECT * FROM customer_orders WHERE farmer_id = ? ORDER BY order_date DESC LIMIT 3");
                $orders_query->bind_param("i", $farmer_id);
                $orders_query->execute();
                $result = $orders_query->get_result();

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Format the date as shown in the image
                        $date_obj = date_create($row['order_date']);
                        $formatted_date = date_format($date_obj, "d/m/Y");
            ?>
                        <!-- Order Card -->
                        <div class="bg-[#0D3D3B] rounded-lg shadow p-4 mb-4 text-white">
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-2">
                                        <span class="text-[#0D3D3B] font-bold">R</span>
                                    </div>
                                    <span class="font-bold"><?= $row['customer_name'] ?></span>
                                </div>
                                <div>
                                    <!-- Three dots menu -->
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4 mb-2">
                                <div>
                                    <p class="text-sm text-gray-300">Location</p>
                                    <p class="text-yellow-400"><?= $row['location'] ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-300">Order Type</p>
                                    <p class="text-yellow-400"><?= $row['order_type'] ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-300">Average Weight</p>
                                    <p><?= $row['average_weight'] ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center mt-4">
                                <div class="w-16 h-16 bg-gray-300 rounded overflow-hidden mr-4">
                                    <?php if (strpos($row['order_type'], 'Juice') !== false): ?>
                                        <img src="pineapple-juice.jpg" alt="Pineapple Juice" class="w-full h-full object-cover">
                                    <?php elseif (strpos($row['order_type'], 'Silk') !== false): ?>
                                        <img src="pineapple-silk.jpg" alt="Pineapple Silk" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <img src="pineapple.jpg" alt="Pineapple" class="w-full h-full object-cover">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex-1 grid grid-cols-3 gap-2">
                                    <div class="bg-yellow-500 rounded p-2 text-center">
                                        <p class="text-xs"><?= $formatted_date ?></p>
                                        <p class="text-xs">Requested</p>
                                    </div>
                                    <div class="bg-gray-700 rounded p-2 text-center">
                                        <p class="text-xs"><?= $row['quantity'] ?></p>
                                        <p class="text-xs">Order Quantity</p>
                                    </div>
                                    <div class="bg-gray-700 rounded p-2 text-center">
                                        <p class="text-xs"><?= $row['price'] ?></p>
                                        <p class="text-xs">Expected Price</p>
                                    </div>
                                </div>
                            </div>
            
            
                        </div>
            <?php
                    }
                } else {
                    echo "<div class='bg-gray-100 rounded-lg p-4 text-center'>No orders available</div>";
                }
                
                // Add pagination controls
                echo '<div class="flex items-center justify-center mt-4 space-x-2">
                    <button class="flex items-center justify-center px-3 py-1 bg-gray-200 rounded-md">
                        <span class="mr-1">Prev</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    
                    <div class="flex space-x-1">
                        <div class="w-6 h-2 bg-[#0D3D3B] rounded-full"></div>
                        <div class="w-6 h-2 bg-gray-300 rounded-full"></div>
                        <div class="w-6 h-2 bg-gray-300 rounded-full"></div>
                    </div>
                    
                    <button class="flex items-center justify-center px-3 py-1 bg-gray-200 rounded-md">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span>Next</span>
                    </button>
                </div>';
                
            } catch (Exception $e) {
                echo "<div class='bg-red-100 rounded-lg p-4 text-center'>Error: " . $e->getMessage() . "</div>";
            }
            ?>
        </div>

        <!-- Stats Section - Right Column -->
        <div class="w-full md:w-1/2 space-y-4">
            <!-- Pinabulaklak -->
            <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4">
                <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Pinabulaklak</div>
                <div class="text-center py-4">
                    <div class="text-6xl font-bold text-white"><?= $flowered ?></div>
                </div>
                <form method="POST" class="mt-2">
                    <div class="flex items-center gap-2">
                        <button name="reset_flowered" type="submit" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium">Reset</button>
                        <input type="number" name="flowered_amount" placeholder="+ " class="flex-grow bg-white rounded p-1 text-left">
                        <button name="add_flowered" type="submit" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium">ADD</button>
                    </div>
                </form>
            </div>

            <!-- Na Peste -->
            <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4">
                <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Na Peste</div>
                <div class="text-center py-4">
                    <div class="text-6xl font-bold text-white"><?= $pested ?></div>
                </div>
                <form method="POST" class="mt-2">
                    <div class="flex items-center gap-2">
                        <button name="reset_pested" type="submit" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium">Reset</button>
                        <input type="number" name="pested_amount" placeholder="+" class="flex-grow bg-white rounded p-1 text-left">
                        <button name="add_pested" type="submit" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium">ADD</button>
                    </div>
                </form>
            </div>

            <!-- Stats with Pie Chart -->
            <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4">
                <div class="flex justify-center h-58 relative">
                    <canvas id="harvestChart" class="max-w-full"></canvas>
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-white text-xl font-bold">
                        <?= $harvested_percent ?>% Harvested
                    </div>
                </div>
                <div class="flex justify-center mt-4 text-white">
                    <div class="flex flex-col items-center">
                        <div class="flex justify-center gap-4 mb-2">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-[#4CAF50] mr-1"></div>
                                <span>Harvested (<?= $harvested_percent ?>%)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-[#FCAE36] mr-1"></div>
                                <span>Damaged (<?= $damaged_percent ?>%)</span>
                            </div>
                        </div>
                        <div>Total Harvest: <?= $total_harvest ?></div>
                    </div>
                </div>
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

<!-- Js for the Harvewst button -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create the harvest pie chart
    const ctx = document.getElementById('harvestChart').getContext('2d');
    const harvestChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Harvested', 'Damaged'],
            datasets: [{
                data: [<?= $flowered ?>, <?= $pested ?>],
                backgroundColor: [
                    '#4CAF50', // Green for harvested
                    '#FCAE36'  // Orange for damaged/pested
                ],
                borderWidth: 0,
                borderRadius: 5
            }]
        },
        options: {
            cutout: '70%',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true
                }
            }
        }
    });
});
</script>

</body>
</html>