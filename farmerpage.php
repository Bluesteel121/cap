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
$flowered = isset($farmer_data['flowered']) ? $farmer_data['flowered'] : 0;
$pested = isset($farmer_data['pested']) ? $farmer_data['pested'] : 0;
$total = $flowered + $pested;
$harvested_percent = $total > 0 ? round(($flowered / $total) * 100) : 0;

// Get farmer name for display
$farmer_name = isset($farmer_data['username']) ? $farmer_data['username'] : 'Unknown Farmer';
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

    <div class="flex flex-col md:flex-row gap-6">
        <!-- Stats Section - Left Column -->
        <div class="w-full md:w-1/3 space-y-4">
            <!-- Pinabulaklak -->
            <div class="bg-[#115D5B] rounded-lg overflow-hidden shadow-lg p-4">
                <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Pinabulaklak</div>
                <div class="text-center py-4">
                    <div class="text-6xl font-bold text-white"><?= $flowered ?></div>
                </div>
                <form method="POST" class="mt-2">
                    <div class="flex items-center">
                        <button name="reset_flowered" type="submit" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium">Harvest</button>
                        <input type="number" name="flowered_amount" placeholder="+" class="mx-2 flex-grow bg-white rounded p-1 text-left">
                        <button name="add_flowered" type="submit" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium">ADD</button>
                    </div>
                </form>
            </div>

            <!-- Na Peste -->
            <div class="bg-[#115D5B] rounded-lg overflow-hidden shadow-lg p-4">
                <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Na Peste</div>
                <div class="text-center py-4">
                    <div class="text-6xl font-bold text-white"><?= $pested ?></div>
                </div>
                <form method="POST" class="mt-2">
                    <div class="flex items-center">
                        <button name="reset_pested" type="submit" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium">Harvest</button>
                        <input type="number" name="pested_amount" placeholder="+" class="mx-2 flex-grow bg-white rounded p-1 text-left">
                        <button name="add_pested" type="submit" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium">ADD</button>
                    </div>
                </form>
            </div>

            <!-- Stats with Pie Chart -->
            <div class="bg-[#115D5B] rounded-lg overflow-hidden shadow-lg p-4">
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
                                <span>Harvested</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-[#FCAE36] mr-1"></div>
                                <span>Damaged</span>
                            </div>
                        </div>
                        <div>Total Harvest: <?= $total ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Harvest Data Table - Right Column -->
        <div class="w-full md:w-2/3 bg-[#115D5B] p-6 rounded-lg border border-gray-300 overflow-y-auto">
            <div class="flex justify-center">
                <input type="text" placeholder="Search" 
                    class="bg-[#103635] w-3/4 p-3 rounded-full mb-4 text-white border border-[#CAEED5] mt-4 focus:border-green-700 focus:ring-2 focus:ring-green-700 focus:outline-none text-center">
            </div>

            <table class="w-full text-black mt-10">
                <thead>
                    <tr class="bg-[#4CAF50] text-white">
                        <th class="p-2 rounded-l-lg">Farmer's Name</th>
                        <th class="p-2">Month Of Harvest</th>
                        <th class="p-2">Possible Harvest</th>
                        <th class="p-2">Quantity</th>
                        <th class="p-2">Location</th>
                        <th class="p-2 rounded-r-lg">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // Check if harvests table exists
                        $table_check = $conn->query("SHOW TABLES LIKE 'harvests'");
                        if ($table_check->num_rows == 0) {
                            // Create harvests table if it doesn't exist
                            $create_table = "CREATE TABLE harvests (
                                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                                farmer_id INT(11) NOT NULL,
                                farmer_name VARCHAR(100) NOT NULL,
                                month_of_harvest VARCHAR(50) NOT NULL,
                                possible_harvest VARCHAR(100) NOT NULL,
                                quantity INT(11) NOT NULL,
                                location VARCHAR(100) NOT NULL,
                                status VARCHAR(50) NOT NULL,
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                            )";
                            
                            if ($conn->query($create_table) === TRUE) {
                                // Insert sample data for this farmer
                                $sample_insert = $conn->prepare("INSERT INTO harvests (farmer_id, farmer_name, month_of_harvest, possible_harvest, quantity, location, status) VALUES 
                                    (?, ?, 'April 2025', 'May 2025', 100, 'North Field', 'Available'),
                                    (?, ?, 'March 2025', 'April 2025', 75, 'South Field', 'Sold')");
                                $sample_insert->bind_param("isis", $farmer_id, $farmer_name, $farmer_id, $farmer_name);
                                $sample_insert->execute();
                            }
                        }
                        
                        // Check if harvests table has farmer_id column
                        $column_check = $conn->query("SHOW COLUMNS FROM harvests LIKE 'farmer_id'");
                        if ($column_check->num_rows == 0) {
                            // Add farmer_id column if it doesn't exist
                            $conn->query("ALTER TABLE harvests ADD COLUMN farmer_id INT(11) NOT NULL AFTER id");
                            // Set all existing records to the first farmer's ID
                            $conn->query("UPDATE harvests SET farmer_id = $farmer_id");
                        }
                        
                        // Get harvests for this farmer
                        $harvest_query = $conn->prepare("SELECT farmer_name, month_of_harvest, possible_harvest, quantity, location, status 
                                                        FROM harvests 
                                                        WHERE farmer_id = ?
                                                        ORDER BY created_at DESC");
                        $harvest_query->bind_param("i", $farmer_id);
                        $harvest_query->execute();
                        $result = $harvest_query->get_result();

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='border-b bg-white'>
                                    <td class='p-2'>{$row['farmer_name']}</td>
                                    <td class='p-2'>{$row['month_of_harvest']}</td>
                                    <td class='p-2'>{$row['possible_harvest']}</td>
                                    <td class='p-2'>{$row['quantity']} kg</td>
                                    <td class='p-2'>{$row['location']}</td>
                                    <td class='p-2 " . ($row['status'] == 'Available' ? 'text-green-600' : ($row['status'] == 'Sold' ? 'text-red-600' : 'text-yellow-600')) . "'>
                                        {$row['status']}
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='p-2 text-center bg-white'>No harvest data available for this farmer</td></tr>";
                        }
                    } catch (Exception $e) {
                        echo "<tr><td colspan='6' class='p-2 text-center bg-white'>Error: " . $e->getMessage() . "</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
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