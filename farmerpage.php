<?php
// Include the shared database connection and functions
require_once 'db_connect.php';

// Get farmer data using the shared function
$farmer_data = getFarmerData($conn);
$farmer_id = $farmer_data['farmer_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Flowering handlers
        if (isset($_POST['add_flowered'])) {
            $amount = intval($_POST['flowered_amount'] ?? 1);
            $stmt = $conn->prepare("UPDATE farmer_acc SET flowered = flowered + ? WHERE farmer_id = ?");
            $stmt->bind_param("ii", $amount, $farmer_id);
            $stmt->execute();
            $farmer_data['flowered'] = ($farmer_data['flowered'] ?? 0) + $amount;
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
        // Pested handlers
        elseif (isset($_POST['add_pested'])) {
            $amount = intval($_POST['pested_amount'] ?? 1);
            $stmt = $conn->prepare("UPDATE farmer_acc SET pested = pested + ? WHERE farmer_id = ?");
            $stmt->bind_param("ii", $amount, $farmer_id);
            $stmt->execute();
            $farmer_data['pested'] = ($farmer_data['pested'] ?? 0) + $amount;
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
        // Planted handlers
        elseif (isset($_POST['add_planted'])) {
            $amount = intval($_POST['planted_amount'] ?? 1);
            $stmt = $conn->prepare("UPDATE farmer_acc SET total_planted = total_planted + ? WHERE farmer_id = ?");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ii", $amount, $farmer_id);
            $stmt->execute();
            $farmer_data['total_planted'] = ($farmer_data['total_planted'] ?? 0) + $amount;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } 
        elseif (isset($_POST['reset_planted'])) {
            if (!isset($farmer_id)) {
                die("Error: farmer_id is not set.");
            }
            $stmt = $conn->prepare("UPDATE farmer_acc SET total_planted = 0 WHERE farmer_id = ?");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("i", $farmer_id);
            $stmt->execute();
            $farmer_data['total_planted'] = 0;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
       
        // Harvest handler
        elseif (isset($_POST['harvest_button'])) {
            $flowered = $farmer_data['flowered'] ?? 0;
            $pested = $farmer_data['pested'] ?? 0;
            $actual_harvest = max(0, $flowered - $pested);
            
            $stmt = $conn->prepare("UPDATE farmer_acc SET last_harvest = ?, flowered = 0, pested = 0 WHERE farmer_id = ?");
            $new_harvest = $actual_harvest . " pcs";
            $stmt->bind_param("si", $new_harvest, $farmer_id);
            $stmt->execute();
            
            $farmer_data['last_harvest'] = $new_harvest;
            $farmer_data['flowered'] = 0;
            $farmer_data['pested'] = 0;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        // Fertilizer handlers
        elseif (isset($_POST['add_fertilizer'])) {
            $month = $_POST['month'];
            $type = $_POST['type'];
            $per_plant = $_POST['per_plant'];
            $sacks = $_POST['sacks'];
            
            // Store fertilizer data as JSON in farmer_acc
            $fertilizer_data = json_decode($farmer_data['fertilizer_data'] ?? '[]', true);
            $fertilizer_data[] = [
                'id' => uniqid(),
                'month' => $month,
                'type' => $type,
                'per_plant_grams' => $per_plant,
                'sacks' => $sacks,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $json_fertilizer = json_encode($fertilizer_data);
            $stmt = $conn->prepare("UPDATE farmer_acc SET fertilizer_data = ? WHERE farmer_id = ?");
            $stmt->bind_param("si", $json_fertilizer, $farmer_id);
            $stmt->execute();
            
            $farmer_data['fertilizer_data'] = $json_fertilizer;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        elseif (isset($_POST['reset_fertilizer'])) {
            $fertilizer_id = $_POST['fertilizer_id'];
            $fertilizer_data = json_decode($farmer_data['fertilizer_data'] ?? '[]', true);
            
            // Filter out the fertilizer with the matching ID
            $filtered_data = array_filter($fertilizer_data, function($item) use ($fertilizer_id) {
                return $item['id'] !== $fertilizer_id;
            });
            
            $json_fertilizer = json_encode(array_values($filtered_data));
            $stmt = $conn->prepare("UPDATE farmer_acc SET fertilizer_data = ? WHERE farmer_id = ?");
            $stmt->bind_param("si", $json_fertilizer, $farmer_id);
            $stmt->execute();
            
            $farmer_data['fertilizer_data'] = $json_fertilizer;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } 
        elseif (isset($_POST['reset_all_fertilizer'])) {
            $stmt = $conn->prepare("UPDATE farmer_acc SET fertilizer_data = '[]' WHERE farmer_id = ?");
            $stmt->bind_param("i", $farmer_id);
            $stmt->execute();
            
            $farmer_data['fertilizer_data'] = '[]';
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        // Pest tracking handlers
        elseif (isset($_POST['add_pest'])) {
            $pest_date = $_POST['pest_date'];
            $pest_type = $_POST['pest_type'];
            $affected_area = $_POST['affected_area'];
            $severity = $_POST['severity'];
            
            // Store pest data as JSON in farmer_acc
            $pest_data = json_decode($farmer_data['pest_data'] ?? '[]', true);
            $pest_data[] = [
                'id' => uniqid(),
                'date' => $pest_date,
                'type' => $pest_type,
                'area' => $affected_area,
                'severity' => $severity,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $json_pest = json_encode($pest_data);
            $stmt = $conn->prepare("UPDATE farmer_acc SET pest_data = ? WHERE farmer_id = ?");
            $stmt->bind_param("si", $json_pest, $farmer_id);
            $stmt->execute();
            
            $farmer_data['pest_data'] = $json_pest;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        elseif (isset($_POST['reset_pest'])) {
            $pest_id = $_POST['pest_id'];
            $pest_data = json_decode($farmer_data['pest_data'] ?? '[]', true);
            
            // Filter out the pest with the matching ID
            $filtered_data = array_filter($pest_data, function($item) use ($pest_id) {
                return $item['id'] !== $pest_id;
            });
            
            $json_pest = json_encode(array_values($filtered_data));
            $stmt = $conn->prepare("UPDATE farmer_acc SET pest_data = ? WHERE farmer_id = ?");
            $stmt->bind_param("si", $json_pest, $farmer_id);
            $stmt->execute();
            
            $farmer_data['pest_data'] = $json_pest;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        elseif (isset($_POST['reset_all_pests'])) {
            $stmt = $conn->prepare("UPDATE farmer_acc SET pest_data = '[]' WHERE farmer_id = ?");
            $stmt->bind_param("i", $farmer_id);
            $stmt->execute();
            
            $farmer_data['pest_data'] = '[]';
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
$total_planted = $farmer_data['total_planted'] ?? 0;
$plantation_area = $farmer_data['plantation_area'] ?? '0.0';
$last_harvest = $farmer_data['last_harvest'] ?? '0 pcs';

// Parse fertilizer data from JSON
$fertilizer_data = json_decode($farmer_data['fertilizer_data'] ?? '[]', true);

// Parse pest data from JSON
$pest_data = json_decode($farmer_data['pest_data'] ?? '[]', true);

// Calculate harvest stats
$actual_harvest = max(0, $flowered - $pested);
$total = $flowered + $pested;
$harvested_percent = $total > 0 ? round(($flowered / $total) * 100) : 0;
$damaged_percent = $total > 0 ? round(($pested / $total) * 100) : 0;

// Farmer display info
$farmer_name = $farmer_data['username'] ?? 'Ricardo Dela Cruz';
$contact_num = $farmer_data['contact_num'] ?? 'rice@gmail.com';
$pending_orders = $farmer_data['pending_orders'] ?? 5;
$profile_pic = displayProfileImage($farmer_data['profile_picture']);
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
<!-- Mobile Sidebar Toggle Button -->
<button id="sidebarToggle" class="fixed top-4 left-4 z-50 bg-[#0D3D3B] p-2 rounded-lg text-white md:hidden">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<!-- Sidebar green - with responsive classes -->
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

        <nav class="mt-6">
            <ul class="space-y-2">
                <li><a href="#" class="flex items-center p-2 bg-[#CAEED5] text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Home</a></li>
                
                <li><a href="farmerprofile.php" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Profile</a></li>

                    <li><a href="farmernotif.php" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Request</a></li>

                <li><a href="farmernotif.php" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Notifications</a></li>
                    
                <li><a href="#" class="flex items-center p-2 text-red-500 hover:text-red-700" onclick="openLogoutModal()">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout</a></li>
            </ul>
        </nav>
    </div>
    <footer class="text-center text-xs">&copy; 2025 Camarines Norte Lowland Rainfed Research Station</footer>
</aside>

<!-- Main Content - with responsive classes -->
<main class="w-full md:w-[calc(100%-256px)] lg:w-3/4 bg-white md:ml-64 lg:ml-[25%] p-4 md:p-6 transition-all duration-300 ease-in-out">
    <!-- Top Cards -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Row 1: Top 3 cards side by side -->
        <!-- Pineapple Planted -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4 overflow-hidden">
            <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Total of Pineapple planted</div>
            <div class="text-center py-6">
                <div class="text-4xl md:text-5xl lg:text-6xl font-bold text-white truncate"><?= number_format($total_planted) ?></div>
            </div>
            <form method="POST" class="mt-2">
                <div class="flex items-center gap-2">
                    <button type="submit" name="reset_planted" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium text-sm whitespace-nowrap">Reset</button>
                    <input type="number" name="planted_amount" placeholder="+" class="flex-grow bg-white rounded p-1 text-left min-w-0">
                    <button type="submit" name="add_planted" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium text-sm whitespace-nowrap">ADD</button>
                </div>
            </form>
        </div>

        <!-- Pinabulaklak -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4 overflow-hidden">
            <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Pinabulaklak</div>
            <div class="text-center py-6">
                <div class="text-4xl md:text-5xl lg:text-6xl font-bold text-white truncate"><?= number_format($flowered) ?></div>
            </div>
            <form method="POST" class="mt-2">
                <div class="flex items-center gap-2">
                    <button name="reset_flowered" type="submit" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium text-sm whitespace-nowrap">Reset</button>
                    <input type="number" name="flowered_amount" placeholder="+" class="flex-grow bg-white rounded p-1 text-left min-w-0">
                    <button name="add_flowered" type="submit" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium text-sm whitespace-nowrap">ADD</button>
                </div>
            </form>
        </div>

        <!-- Na Peste -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4 overflow-hidden">
            <div class="text-center bg-[#CAEED5] py-2 font-semibold text-green-800 rounded">Na Peste</div>
            <div class="text-center py-6">
                <div class="text-4xl md:text-5xl lg:text-6xl font-bold text-white truncate"><?= $pested ?></div>
            </div>
            <form method="POST" class="mt-2">
                <div class="flex items-center gap-2">
                    <button name="reset_pested" type="submit" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium text-sm whitespace-nowrap">Reset</button>
                    <input type="number" name="pested_amount" placeholder="+" class="flex-grow bg-white rounded p-1 text-left min-w-0">
                    <button name="add_pested" type="submit" class="bg-[#4CAF50] px-3 py-1 rounded text-white font-medium text-sm whitespace-nowrap">ADD</button>
                </div>
            </form>
        </div>

        <!-- Row 2: Two tables side by side -->
        <!-- Fertilizer Usage -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4 md:col-span-1 lg:col-span-1.5 overflow-hidden">
            <!-- Header -->
            <div class="text-center bg-[#CAEED5] py-1.5 text-sm font-medium text-green-800 rounded">
                Fertilizer Usage
            </div>
            
            <!-- Table container with adaptive height -->
            <div class="mt-3 overflow-y-auto h-52 md:h-52 lg:h-52">
                <table class="w-full text-white table-fixed">
                    <thead class="bg-[#115D5B] sticky top-0">
                        <tr>
                            <th class="px-2 py-1 text-sm w-1/5">Month</th>
                            <th class="w-1/5">Type</th>
                            <th class="w-1/5">Per Plant</th>
                            <th class="w-1/5">Sacks</th>
                            <th class="w-1/5">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fertilizer_data)): ?>
                            <?php foreach ($fertilizer_data as $entry): ?>
                                <tr class="text-center border-b border-[#115D5B]">
                                    <td class="p-2 truncate"><?= htmlspecialchars($entry['month']) ?></td>
                                    <td class="truncate"><?= htmlspecialchars($entry['type']) ?></td>
                                    <td class="truncate"><?= $entry['per_plant_grams'] ?></td>
                                    <td class="truncate"><?= $entry['sacks'] ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="fertilizer_id" value="<?= $entry['id'] ?>">
                                            <button type="submit" name="reset_fertilizer" class="font-bold text-red-400 hover:text-red-600 bg-[#CAEED5] p-1 rounded-lg text-xs">Reset</button>
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

            <div class="flex items-center gap-2 mt-4">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="reset_all_fertilizer" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium text-sm whitespace-nowrap">Reset All</button>
                </form>
                <input 
                    type="text" 
                    placeholder="+" 
                    class="flex-grow bg-white rounded p-1 text-left cursor-pointer min-w-0"
                    onclick="openFertilizerForm()"
                    readonly
                >
            </div>
        </div>

        <!-- Pest Types Tracking Table -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4 md:col-span-1 lg:col-span-1.5 overflow-hidden">
            <!-- Header -->
            <div class="text-center bg-[#CAEED5] py-1.5 text-sm font-medium text-green-800 rounded">
                Pest Types Tracking
            </div>
            
            <!-- Table container with adaptive height -->
            <div class="mt-3 overflow-y-auto h-52 md:h-52 lg:h-52">
                <table class="w-full text-white table-fixed">
                    <thead class="bg-[#115D5B] sticky top-0">
                        <tr>
                            <th class="px-2 py-1 text-sm w-1/5">Date</th>
                            <th class="w-1/5">Pest Type</th>
                            <th class="w-1/5">Area</th>
                            <th class="w-1/5">Severity</th>
                            <th class="w-1/5">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pest_data)): ?>
                            <?php foreach ($pest_data as $pest): ?>
                                <tr class="text-center border-b border-[#115D5B]">
                                    <td class="p-2 truncate"><?= htmlspecialchars($pest['date']) ?></td>
                                    <td class="truncate"><?= htmlspecialchars($pest['type']) ?></td>
                                    <td class="truncate"><?= htmlspecialchars($pest['area']) ?></td>
                                    <td class="truncate"><?= htmlspecialchars($pest['severity']) ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="pest_id" value="<?= $pest['id'] ?>">
                                            <button type="submit" name="reset_pest" class="font-bold text-red-400 hover:text-red-600 bg-[#CAEED5] p-1 rounded-lg text-xs">Reset</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-400">No pest records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center gap-2 mt-4">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="reset_all_pests" class="bg-[#FCAE36] px-3 py-1 rounded text-black font-medium text-sm whitespace-nowrap">Reset All</button>
                </form>
                <input 
                    type="text" 
                    placeholder="+" 
                    class="flex-grow bg-white rounded p-1 text-left cursor-pointer min-w-0"
                    onclick="openPestForm()"
                    readonly
                >
            </div>
        </div>

        <!-- Row 3: Harvest Chart (Full Width) -->
        <div class="bg-[#0D3D3B] rounded-lg shadow-lg p-4 overflow-hidden">
        <div class="flex justify-center h-40 md:h-48 lg:h-40 relative">
            <canvas id="harvestChart" class="max-w-full max-h-full"></canvas>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-white text-xl font-bold">
                <?= $harvested_percent ?>% Harvested
            </div>
        </div>
        <div class="flex justify-center mt-2 text-white">
            <div class="flex flex-col items-center">
                <div class="flex flex-wrap justify-center gap-4 mb-1">
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
                <form method="POST" class="mt-2">
                    <button name="harvest_button" type="submit" class="bg-[#4CAF50] px-4 py-2 rounded text-white font-medium">Harvest</button>
                </form>
            </div>
        </div>
    </div>
</div>
</main>

<!-- Fertilizer Form Modal -->
<div id="fertilizerForm" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md mx-4">
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

<!-- Pest Form Modal -->
<div id="pestForm" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md mx-4">
        <h3 class="text-xl font-bold mb-4">Add Pest Tracking Data</h3>
        <form method="POST">
            <input type="date" name="pest_date" class="w-full p-2 mb-2 border rounded" required>
            <select name="pest_type" class="w-full p-2 mb-2 border rounded" required>
                <option value="">Select Pest Type</option>
                <option value="Mealybugs">Mealybugs</option>
                <option value="Pineapple Scale">Pineapple Scale</option>
                <option value="Nematodes">Nematodes</option>
                <option value="Fruit Borer">Fruit Borer</option>
                <option value="White Grubs">White Grubs</option>
                <option value="Thrips">Thrips</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" name="affected_area" placeholder="Affected Area (e.g., North Field)" class="w-full p-2 mb-2 border rounded" required>
            <select name="severity" class="w-full p-2 mb-2 border rounded" required>
                <option value="">Select Severity</option>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
                <option value="Critical">Critical</option>
            </select>
            <div class="flex gap-2 mt-4">
                <button type="button" onclick="closePestForm()" class="flex-1 bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
                <button type="submit" name="add_pest" class="flex-1 bg-[#4CAF50] text-white px-4 py-2 rounded">Add</button>
            </div>
        </form>
    </div>
</div>

<script>
function openFertilizerForm() {
    document.getElementById('fertilizerForm').classList.remove('hidden');
}

function closeFertilizerForm() {
    document.getElementById('fertilizerForm').classList.add('hidden');
}

function openPestForm() {
    document.getElementById('pestForm').classList.remove('hidden');
}

function closePestForm() {
    document.getElementById('pestForm').classList.add('hidden');
}

// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('main');
    
    // Toggle sidebar on button click
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('-translate-x-full');
        
        // If sidebar is visible, add a click event listener to the document
        if (!sidebar.classList.contains('-translate-x-full')) {
            setTimeout(() => {
                document.addEventListener('click', closeSidebarOnClickOutside);
            }, 100);
        }
    });
    
    // Function to close sidebar when clicking outside
    function closeSidebarOnClickOutside(event) {
        // Check if clicked element is not part of the sidebar or toggle button
        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.add('-translate-x-full');
            document.removeEventListener('click', closeSidebarOnClickOutside);
        }
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) { // md breakpoint
            sidebar.classList.remove('-translate-x-full');
        } else {
            sidebar.classList.add('-translate-x-full');
        }
    });
});
</script>

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