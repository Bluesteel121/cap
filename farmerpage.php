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

// Create plantation_details table if it doesn't exist
try {
    // Check if plantation_details table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'plantation_details'");
    if ($table_check->num_rows == 0) {
        // Create plantation_details table if it doesn't exist
        $create_table = "CREATE TABLE plantation_details (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            farmer_id INT(11) NOT NULL,
            area VARCHAR(50) NOT NULL,
            last_harvest VARCHAR(50) NOT NULL,
            total_planted INT(11) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($create_table) === TRUE) {
            // Insert sample data for this farmer
            $sample_insert = $conn->prepare("INSERT INTO plantation_details 
                (farmer_id, area, last_harvest, total_planted) VALUES 
                (?, '2.5', '10000 pcs', 50000)");
            $sample_insert->bind_param("i", $farmer_id);
            $sample_insert->execute();
        }
    }
    
    // Get plantation data for this farmer
    $plantation_query = $conn->prepare("SELECT * FROM plantation_details WHERE farmer_id = ?");
    $plantation_query->bind_param("i", $farmer_id);
    $plantation_query->execute();
    $plantation_result = $plantation_query->get_result();
    
    if ($plantation_result && $plantation_row = $plantation_result->fetch_assoc()) {
        $plantation_area = $plantation_row['area'];
        $last_harvest = $plantation_row['last_harvest'];
        $total_planted = $plantation_row['total_planted'];
    } else {
        // Default values if no data found
        $plantation_area = "0.0";
        $last_harvest = "0 pcs";
        $total_planted = 0;
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
            // Store the previous value for reference
            $previous_flowered = isset($farmer_data['flowered']) ? $farmer_data['flowered'] : 0;
            
            $update_query = $conn->prepare("UPDATE farmer_acc SET flowered = 0 WHERE farmer_id = ?");
            $update_query->bind_param("i", $farmer_id);
            $update_query->execute();
            // Update local data
            $farmer_data['flowered'] = 0;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } elseif (isset($_POST['reset_pested'])) {
            // Store the previous value for reference
            $previous_pested = isset($farmer_data['pested']) ? $farmer_data['pested'] : 0;
            
            $update_query = $conn->prepare("UPDATE farmer_acc SET pested = 0 WHERE farmer_id = ?");
            $update_query->bind_param("i", $farmer_id);
            $update_query->execute();
            // Update local data
            $farmer_data['pested'] = 0;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } elseif (isset($_POST['harvest_button'])) {
            // Get current flowered and pested values
            $flowered = isset($farmer_data['flowered']) ? $farmer_data['flowered'] : 0;
            $pested = isset($farmer_data['pested']) ? $farmer_data['pested'] : 0;
            
            // Calculate actual harvest (flowered - pested)
            $actual_harvest = max(0, $flowered - $pested); // Make sure it's not negative
            
            // Update last_harvest with the calculated value
            $update_query = $conn->prepare("UPDATE plantation_details SET last_harvest = ? WHERE farmer_id = ?");
            $new_harvest = $actual_harvest . " pcs";
            $update_query->bind_param("si", $new_harvest, $farmer_id);
            $update_query->execute();
            
            // Update local variable
            $last_harvest = $new_harvest;
            
            // Reset both flowered and pested to 0 after harvest
            $reset_query = $conn->prepare("UPDATE farmer_acc SET flowered = 0, pested = 0 WHERE farmer_id = ?");
            $reset_query->bind_param("i", $farmer_id);
            $reset_query->execute();
            $farmer_data['flowered'] = 0;
            $farmer_data['pested'] = 0;
            
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    } catch (Exception $e) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Error updating stats: " . $e->getMessage() . "</div>";
    }
}

// Get stats values with fallbacks to zero if not set
$flowered = isset($farmer_data['flowered']) ? $farmer_data['flowered'] : 10000;
$pested = isset($farmer_data['pested']) ? $farmer_data['pested'] : 28;

// Calculate the actual harvest (flowered - pested), ensure it's not negative
$actual_harvest = max(0, $flowered - $pested);
$total = $flowered + $pested; // Total for percentage calculations

// Calculate percentages
if ($total > 0) {
    $harvested_percent = round(($flowered / $total) * 100);
    $damaged_percent = round(($pested / $total) * 100);
} else {
    $harvested_percent = 65; // Default values
    $damaged_percent = 35;
}

// Get farmer name for display
$farmer_name = isset($farmer_data['username']) ? $farmer_data['username'] : 'Ricardo Dela Cruz';
$contact_num = isset($farmer_data['contact_num']) ? $farmer_data['contact_num'] : 'rice@gmail.com';

// Sample stats for top cards
$pending_orders = 5;
?>

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

<!-- Sidebar -->
<aside class="w-1/4 bg-[#115D5B] p-6 h-screen flex flex-col justify-between text-white">
    <div>
        <div class="flex flex-col items-center text-center">
            <?php 
            $profile_pic = !empty($farmer_data['profile_picture']) ? $farmer_data['profile_picture'] : 'profile.jpg';
            ?>
            <img src="<?= $profile_pic ?>" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
            <h2 class="font-bold"><?= $farmer_name ?></h2>
            <p class="text-sm"><?= $contact_num ?></p>
            <p class="text-sm italic">Farmer</p>
            <?php if(isset($farmer_data['status'])): ?>
                <p class="text-xs mt-1 px-2 py-1 rounded-full <?= $farmer_data['status'] == 'Active' ? 'bg-green-600' : 'bg-red-600' ?>">
                    <?= $farmer_data['status'] ?>
                </p>
            <?php endif; ?>
        </div>
        <nav class="mt-6">
            <ul class="space-y-2">
                <li><a href="#" class="flex items-center justify-center p-2 bg-[#CAEED5] text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Home</a>
                </li>
                <li><a href="#" class="flex items-center justify-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile</a>
                </li>
                <li><a href="#" class="flex items-center justify-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Notifications</a>
                </li>
                <li><a href="#" class="flex items-center justify-center p-2 text-red-500 hover:text-red-700" onclick="openLogoutModal()">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout</a>
                </li>
            </ul>
        </nav>
    </div>
    <footer class="text-center text-xs">&copy; 2025 Camarines Norte Lowland Rainfed Research Station</footer>
</aside>

<!-- Main Content -->
<main class="w-3/4 p-6 bg-white">

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
                <div class="bg-[#0D3D3B] p-3 rounded-full flex items-center justify-center">
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
                <div class="bg-[#0D3D3B] p-3 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold text-xl">T</span>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold"><?= $last_harvest ?></h2>
                    <p class="text-gray-700">Last Harvest</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Total of Pineapple Planted -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4">
            <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Total of Pineapple planted</div>
            <div class="text-center py-6">
                <div class="text-6xl font-bold text-white"><?= number_format($total_planted) ?></div>
            </div>
            <div class="flex items-center gap-2 mt-2">
                <button type="button" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium">Reset</button>
                <input type="number" placeholder="+" class="flex-grow bg-white rounded p-1 text-left">
                <button type="button" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium">ADD</button>
            </div>
        </div>

        <!-- Pinabulaklak Section -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4">
            <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Pinabulaklak</div>
            <div class="text-center py-6">
                <div class="text-6xl font-bold text-white"><?= number_format($flowered) ?></div>
            </div>
            <form method="POST" class="mt-2">
                <div class="flex items-center gap-2">
                    <button name="reset_flowered" type="submit" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium">Reset</button>
                    <input type="number" name="flowered_amount" placeholder="+" class="flex-grow bg-white rounded p-1 text-left">
                    <button name="add_flowered" type="submit" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium">ADD</button>
                </div>
            </form>
        </div>

        <!-- Fertilizer Usage Section -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4">
            <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Fertilizer Usage</div>
            <div class="flex flex-col space-y-2 mt-3 max-h-80 overflow-y-auto">
                <?php for($i=0; $i<6; $i++): ?>
                <div class="bg-[#CAEED5] p-2 rounded">
                    Total of Pineapple planted
                </div>
                <?php endfor; ?>
            </div>
            <div class="flex items-center gap-2 mt-4">
                <button type="button" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium">Reset</button>
                <input type="text" placeholder="+" class="flex-grow bg-white rounded p-1 text-left">
                <button type="button" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium">ADD</button>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="flex flex-col space-y-4">
            <!-- Na Peste -->
            <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4">
                <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Na Peste</div>
                <div class="text-center py-6">
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
                        <div>Total Harvest: <?= $actual_harvest ?></div>
                        
                        <!-- Harvest Button -->
                        <form method="POST" class="mt-3">
                            <button name="harvest_button" type="submit" class="bg-[#4CAF50] px-4 py-2 rounded text-white font-medium">Harvest</button>
                        </form>
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

<!-- Chart.js for the Harvest chart -->
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