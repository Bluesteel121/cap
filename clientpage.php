<?php
// Start the session if it's not already started
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "capstone");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['username']) && !isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: account.php");
    exit();
}

// Get the login identifier (either username or email)
$login_identifier = isset($_SESSION['username']) ? $_SESSION['username'] : $_SESSION['email'];

// Get user data from database using the login identifier
if (isset($_SESSION['username'])) {
    $sql = "SELECT full_name, email FROM client_acc WHERE username = ?";
} else {
    $sql = "SELECT full_name, email FROM client_acc WHERE email = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login_identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $full_name = $user_data['full_name'];
    $email = $user_data['email'];
    // Setting a default user type
    $user_type = "Client"; 
} else {
    // Handle case where user data is not found
    $full_name = "User";
    $email = $login_identifier; // Show the login identifier as email if full name not found
    $user_type = "User";
}

$stmt->close();

// Real-time filtering by current month (January to December)
$currentMonth = date('F');
$months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
$filteredMonths = array_slice($months, array_search($currentMonth, $months));

$placeholders = rtrim(str_repeat('?,', count($filteredMonths)), ',');
$sql = "SELECT farmer_name, month_of_harvest, possible_harvest, quantity, location, status 
        FROM harvests 
        WHERE month_of_harvest IN ($placeholders)
        ORDER BY FIELD(month_of_harvest, " . implode(',', array_fill(0, count($filteredMonths), '?')) . ")
        LIMIT 20";

$stmt = $conn->prepare($sql);
$params = array_merge($filteredMonths, $filteredMonths);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pineapple Crops Price</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        function filterTable() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let rows = document.querySelectorAll('.harvest-row');
            rows.forEach(row => {
                let content = row.innerText.toLowerCase();
                row.style.display = content.includes(input) ? '' : 'none';
            });
        }
    </script>
</head>
<body class="flex">
    <!-- Sidebar -->
    <aside class="w-1/4 bg-[#115D5B] p-6 h-screen flex flex-col justify-between text-white fixed">
        <div>
            <div class="flex flex-col items-center text-center">
                <img src="profile.jpg" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
                <h2 class="font-bold"><?php echo htmlspecialchars($full_name); ?></h2>
                <p class="text-sm"><?php echo htmlspecialchars($email); ?></p>
                <p class="text-sm italic"><?php echo htmlspecialchars($user_type); ?></p>
            </div>
            <nav class="mt-6">
                <ul class="space-y-2">
                    <li><a href="#" class="block p-2 bg-[#CAEED5] text-green-700 rounded hover:bg-gray-300">Home</a></li>
                    <li><a href="clientorder.php" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Order</a></li>
                    <li><a href="#" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Notifications</a></li>
                    <li><a href="#" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Profile</a></li>
                    <li><a href="#" class="block p-2 text-red-500 hover:text-red-700" onclick="openLogoutModal()">Logout</a></li>
                </ul>
            </nav>
        </div>
        <footer class="text-center text-xs">&copy; 2025 Camarines Norte Lowland Rainfed Research Station</footer>
    </aside>
    
    <!-- Main Content -->
    <main class="w-3/4 p-6 bg-white ml-[25%]">
        <header class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-green-700">Pineapple Crops Price</h1>
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Place Order</button>
        </header>
        
        <div class="grid grid-cols-3 gap-6 text-white font-bold mb-6">
            <div class="bg-[#115D5B] p-4 rounded-lg flex items-center">
                <img src="Images\pineapple-fruit.jpg" alt="Pineapple Fruit" class="w-16 h-16 rounded-lg mr-4">
                <div>
                    <h3>Pineapple Fruit</h3>
                    <p class="text-lg">₱50-60 Per Piece</p>
                </div>
            </div>
            <div class="bg-[#115D5B] p-4 rounded-lg flex items-center">
                <img src="Images\pineapple-juice.jpg" alt="Pineapple Juice" class="w-16 h-16 rounded-lg mr-4">
                <div>
                    <h3>Pineapple Juice</h3>
                    <p class="text-lg">₱50-60 Per Liter</p>
                </div>
            </div>
            <div class="bg-[#115D5B] p-4 rounded-lg flex items-center">
                <img src="Images\pineapple-fiber2.png" alt="Pineapple Fiber" class="w-16 h-16 rounded-lg mr-4">
                <div>
                    <h3>Pineapple Fiber</h3>
                    <p class="text-lg">₱50-60 Per Yard</p>
                </div>
            </div>
        </div>

     
       

        <!-- DiV -->
        <div class="bg-[#115D5B] p-6 rounded-lg border border-gray-300 overflow-y-auto">
               <!-- Search Bar -->
        <div class="flex justify-center">
            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search"
                class="bg-[#103635] w-3/4 p-3 rounded-full mb-4 text-white border-[2.5px] border-[#4CAF50] mt-4 focus:border-green-700 focus:ring-2 focus:ring-green-700 focus:outline-none text-center">
        </div>   
            <!-- Table -->
            <div class="space-y-4 mt-10">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='harvest-row bg-[#103635] bg-opacity-50 border-[2.5px] border-[#4CAF50] p-4 rounded-lg shadow-md flex items-center justify-between'>
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
                            </div>";
                    }
                } else {
                    echo "<div class='p-4 text-center text-gray-500 bg-white rounded-lg shadow-md'>No Data Available</div>";
                }
                $conn->close();
                ?>
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
