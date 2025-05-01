<?php
session_start();
$conn = new mysqli("localhost", "root", "", "capstone");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get farmer information
$farmer_id = isset($_SESSION['farmer_id']) ? $_SESSION['farmer_id'] : 1;
$farmer_data = [];
$fertilizer_data = [];

try {
    // Get farmer account data from data base
    $stmt = $conn->prepare("SELECT * FROM farmer_acc WHERE farmer_id = ?");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $farmer_data = $row;
    } else {
        $result = $conn->query("SELECT * FROM farmer_acc LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $farmer_data = $row;
            $farmer_id = $row['farmer_id'];
        }
    }
} catch (Exception $e) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Error: " . $e->getMessage() . "</div>";
}

// Create tables if not exists
try {
    // Create plantation_details
    $conn->query("CREATE TABLE IF NOT EXISTS plantation_details (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        farmer_id INT(11) NOT NULL,
        area VARCHAR(50) NOT NULL,
        last_harvest VARCHAR(50) NOT NULL,
        total_planted INT(11) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Create fertilizer_usage
    $conn->query("CREATE TABLE IF NOT EXISTS fertilizer_usage (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        farmer_id INT(11) NOT NULL,
        month VARCHAR(20) NOT NULL,
        type VARCHAR(50) NOT NULL,
        per_plant_grams DECIMAL(10,2) NOT NULL,
        sacks INT(11) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Get plantation data
    $plantation_query = $conn->prepare("SELECT * FROM plantation_details WHERE farmer_id = ?");
    $plantation_query->bind_param("i", $farmer_id);
    $plantation_query->execute();
    $plantation_result = $plantation_query->get_result();
    
    if ($plantation_result && $plantation_row = $plantation_result->fetch_assoc()) {
        $plantation_area = $plantation_row['area'];
        $last_harvest = $plantation_row['last_harvest'];
        $total_planted = $plantation_row['total_planted'];
    } else {
        $plantation_area = "0.0";
        $last_harvest = "0 pcs";
        $total_planted = 0;
    }

    // Get fertilizer data
    $fertilizer_query = $conn->prepare("SELECT * FROM fertilizer_usage WHERE farmer_id = ?");
    $fertilizer_query->bind_param("i", $farmer_id);
    $fertilizer_query->execute();
    $fertilizer_result = $fertilizer_query->get_result();
    $fertilizer_data = $fertilizer_result->fetch_all(MYSQLI_ASSOC) ?: [];

} catch (Exception $e) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Error: " . $e->getMessage() . "</div>";
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Existing handlers
        if (isset($_POST['add_flowered'])) {
            $amount = intval($_POST['flowered_amount'] ?? 1);
            $stmt = $conn->prepare("UPDATE farmer_acc SET flowered = flowered + ? WHERE farmer_id = ?");
            $stmt->bind_param("ii", $amount, $farmer_id);
            $stmt->execute();
            $farmer_data['flowered'] = ($farmer_data['flowered'] ?? 0) + $amount;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } 
        elseif (isset($_POST['add_pested'])) {
            $amount = intval($_POST['pested_amount'] ?? 1);
            $stmt = $conn->prepare("UPDATE farmer_acc SET pested = pested + ? WHERE farmer_id = ?");
            $stmt->bind_param("ii", $amount, $farmer_id);
            $stmt->execute();
            $farmer_data['pested'] = ($farmer_data['pested'] ?? 0) + $amount;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } 
        elseif (isset($_POST['reset_flowered'])) {
            $stmt = $conn->prepare("UPDATE farmer_acc SET flowered = 0 WHERE farmer_id = ?");
            $stmt->bind_param("i", $farmer_id);
            $stmt->execute();
            $farmer_data['flowered'] = 0;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } 
        elseif (isset($_POST['reset_pested'])) {
            $stmt = $conn->prepare("UPDATE farmer_acc SET pested = 0 WHERE farmer_id = ?");
            $stmt->bind_param("i", $farmer_id);
            $stmt->execute();
            $farmer_data['pested'] = 0;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } 
        elseif (isset($_POST['harvest_button'])) {
            $flowered = $farmer_data['flowered'] ?? 0;
            $pested = $farmer_data['pested'] ?? 0;
            $actual_harvest = max(0, $flowered - $pested);
            
            $stmt = $conn->prepare("UPDATE plantation_details SET last_harvest = ? WHERE farmer_id = ?");
            $new_harvest = $actual_harvest . " pcs";
            $stmt->bind_param("si", $new_harvest, $farmer_id);
            $stmt->execute();
            
            $stmt = $conn->prepare("UPDATE farmer_acc SET flowered = 0, pested = 0 WHERE farmer_id = ?");
            $stmt->bind_param("i", $farmer_id);
            $stmt->execute();
            $farmer_data['flowered'] = 0;
            $farmer_data['pested'] = 0;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        // Fertilizer handler
        elseif (isset($_POST['add_fertilizer'])) {
            $month = $_POST['month'];
            $type = $_POST['type'];
            $per_plant = $_POST['per_plant'];
            $sacks = $_POST['sacks'];

            $stmt = $conn->prepare("INSERT INTO fertilizer_usage (farmer_id, month, type, per_plant_grams, sacks) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issdi", $farmer_id, $month, $type, $per_plant, $sacks);
            $stmt->execute();
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        // Place this alongside your other fertilizer handlers
elseif (isset($_POST['reset_fertilizer'])) {
    $fertilizer_id = intval($_POST['fertilizer_id']);
    $stmt = $conn->prepare("DELETE FROM fertilizer_usage WHERE id = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $fertilizer_id, $farmer_id);
    $stmt->execute();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
} 
elseif (isset($_POST['reset_all_fertilizer'])) {
    $stmt = $conn->prepare("DELETE FROM fertilizer_usage WHERE farmer_id = ?");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
//total Planted
elseif (isset($_POST['add_planted'])) {
    $amount = intval($_POST['planted_amount'] ?? 1);
    $stmt = $conn->prepare("UPDATE plantation_details SET total_planted = total_planted + ? WHERE farmer_id = ?");
    $stmt->bind_param("ii", $amount, $farmer_id);
    $stmt->execute();
    
    // If no rows were updated, insert a new row
    if ($stmt->affected_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO plantation_details (farmer_id, area, last_harvest, total_planted) VALUES (?, '0.0', '0 pcs', ?)");
        $stmt->bind_param("ii", $farmer_id, $amount);
        $stmt->execute();
    }
    
    // Update the variable for the page
    $total_planted += $amount;
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
} 
elseif (isset($_POST['reset_planted'])) {
    $stmt = $conn->prepare("UPDATE plantation_details SET total_planted = 0 WHERE farmer_id = ?");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    
    // Update the variable for the page
    $total_planted = 0;
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

    } catch (Exception $e) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Error: " . $e->getMessage() . "</div>";
    }
}


// Calculate stats
$flowered = $farmer_data['flowered'] ?? 0;
$pested = $farmer_data['pested'] ?? 0;
$actual_harvest = max(0, $flowered - $pested);
$total = $flowered + $pested;
$harvested_percent = $total > 0 ? round(($flowered / $total) * 100) : 65;
$damaged_percent = $total > 0 ? round(($pested / $total) * 100) : 35;

// Farmer display info
$farmer_name = $farmer_data['username'] ?? 'Ricardo Dela Cruz';
$contact_num = $farmer_data['contact_num'] ?? 'rice@gmail.com';
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
        function openLogoutModal() { document.getElementById('logout-modal').classList.remove('hidden'); }
        function closeLogoutModal() { document.getElementById('logout-modal').classList.add('hidden'); }
        function confirmLogout() { window.location.href = 'account.php'; }
        
        // Fertilizer functions
        function openFertilizerForm() { document.getElementById('fertilizerForm').classList.remove('hidden'); }
        function closeFertilizerForm() { document.getElementById('fertilizerForm').classList.add('hidden'); }
    </script>
</head>
<body class="flex">
<!-- Sidebar green -->
<aside class="w-1/4 bg-[#115D5B] p-6 h-screen fixed top-0 left-0 flex flex-col justify-between text-white">
    <div>
        <div class="flex flex-col items-center text-center">
            <?php $profile_pic = $farmer_data['profile_picture'] ?? 'profile.jpg'; ?>
            <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
            <h2 class="font-bold"><?= htmlspecialchars($farmer_name) ?></h2>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile</a></li>
                <li><a href="#" class="flex items-center  p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
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
    <!-- Top Cards -->
    <div class="flex gap-4 mb-6">
        <div class="flex-1 bg-[#CAEED5] p-4 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-[#0D3D3B] p-3 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold"><?= $pending_orders ?></h2>
                    <p class="text-gray-700">Pending Orders</p>
                </div>
            </div>
        </div>
        <div class="flex-1 bg-[#CAEED5] p-4 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-[#0D3D3B] p-3 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold text-xl">A</span>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold"><?= htmlspecialchars($plantation_area) ?></h2>
                    <p class="text-gray-700">Area of Plantation</p>
                </div>
            </div>
        </div>
        <div class="flex-1 bg-[#CAEED5] p-4 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-[#0D3D3B] p-3 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold text-xl">T</span>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold"><?= htmlspecialchars($last_harvest) ?></h2>
                    <p class="text-gray-700">Last Harvest</p>
                </div>
            </div>
        </div>
    </div>



    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pineapple Planted -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4">
    <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Total of Pineapple planted</div>
    <div class="text-center py-6">
        <div class="text-6xl font-bold text-white"><?= number_format($total_planted) ?></div>
    </div>
    <form method="POST" class="mt-2">
        <div class="flex items-center gap-2">
            <button type="submit" name="reset_planted" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium">Reset</button>
            <input type="number" name="planted_amount" placeholder="+" class="flex-grow bg-white rounded p-1 text-left">
            <button type="submit" name="add_planted" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium">ADD</button>
        </div>
    </form>
</div>

        <!-- Pinabulaklak -->
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

        <!-- Fertilizer Usage -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4">
       <div class="text-center bg-[#CAEED5] py-1.5 text-sm font-medium text-green-800 rounded">Fertilizer Usage</div>
    <div class="mt-3 overflow-y-auto" style="max-height: <?php echo !empty($fertilizer_data) ? '80vh' : 'auto'; ?>">
        <table class="w-full text-white">
            <thead class="bg-[#115D5B]">
                <tr>
                <th class="px-2 py-1 text-sm">Month</th>
                    <th>Type</th>
                    <th>Per Plant (g)</th>
                    <th>Sacks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($fertilizer_data)): ?>
                    <?php foreach ($fertilizer_data as $entry): ?>
                        <tr class="text-center border-b border-[#115D5B]">
                            <td class="p-2"><?= htmlspecialchars($entry['month']) ?></td>
                            <td><?= htmlspecialchars($entry['type']) ?></td>
                            <td><?= number_format($entry['per_plant_grams'], 2) ?>g</td>
                            <td><?= htmlspecialchars($entry['sacks']) ?> sacks</td>
                            <td class="px-2 py-1"><?= htmlspecialchars($entry['month']) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="fertilizer_id" value="<?= $entry['id'] ?>">
                                    <button type="submit" name="reset_fertilizer" class="font-bold text-red-400 hover:text-red-600  bg-[#CAEED5] p-1 rounded-lg">Reset</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-400">No fertilizer records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Fertilizer Form -->
    <div id="fertilizerForm" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h3 class="text-xl font-bold mb-4">Add Fertilizer Data</h3>
            <form method="POST">
                <input type="month" name="month" class="w-full p-2 mb-2 border rounded" required>
                <input type="text" name="type" placeholder="Fertilizer Type" class="w-full p-2 mb-2 border rounded" required>
                <input type="number" step="0.01" name="per_plant" placeholder="Grams per plant" class="w-full p-2 mb-2 border rounded" required>
                <input type="number" name="sacks" placeholder="Number of sacks" class="w-full p-2 mb-2 border rounded" required>
                <div class="flex gap-2 mt-4">
                    <button type="button" onclick="closeFertilizerForm()" class="flex-1 bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" name="add_fertilizer" class="flex-1 bg-[#4CAF50] text-white px-4 py-2 rounded">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="flex items-center gap-2 mt-4">
        <form method="POST" style="display: inline;">
            <button type="submit" name="reset_all_fertilizer" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium">Reset All</button>
        </form>
        <input 
            type="text" 
            placeholder="+" 
            class="flex-grow bg-white rounded p-1 text-left cursor-pointer"
            onclick="openFertilizerForm()"
            readonly
        >
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

            <!-- Harvest Chart -->
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
                        <form method="POST" class="mt-3">
                            <button name="harvest_button" type="submit" class="bg-[#4CAF50] px-4 py-2 rounded text-white font-medium">Harvest</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
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
    const ctx = document.getElementById('harvestChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Harvested', 'Damaged'],
            datasets: [{
                data: [<?= $flowered ?>, <?= $pested ?>],
                backgroundColor: ['#4CAF50', '#FCAE36'],
                borderWidth: 0,
                borderRadius: 5
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            }
        }
    });
});
</script>
</body>
</html>