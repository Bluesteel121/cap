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
    </script>
</head>
<body class="flex">
    <!-- Sidebar -->
    <aside class="w-1/4 bg-[#115D5B] p-6 h-screen flex flex-col justify-between text-white">
        <div>
            <div class="flex flex-col items-center text-center">
                <img src="profile.jpg" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
                <h2 class="font-bold">Ricardo Dela Cruz</h2>
                <p class="text-sm">jpcn@gmail.com</p>
                <p class="text-sm italic">Customer</p>
            </div>
            <nav class="mt-6 ">
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
    <main class="w-3/4 p-6 bg-white">
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

        
       
       
        <div class="bg-[#115D5B] p-6 rounded-lg border border-gray-300 overflow-y-auto">
        <div class="flex justify-center">
    <input type="text" placeholder="Search" 
        class="bg-[#103635] w-3/4 p-3 rounded-full mb-4 text-white border border-[#CAEED5] mt-4 focus:border-green-700 focus:ring-2 focus:ring-green-700 focus:outline-none text-center">
</div>

            <table class="w-full text-black  mt-10">
                <thead>
                    <tr class="bg-[#4CAF50] border-white-300 text-white rounded-lg">
                        <th class="p-2 rounded-l-lg">Farmer’s Name</th>
                        <th class="p-2">Month Of Harvest</th>
                        <th class="p-2">Possible Harvest</th>
                        <th class="p-2">Quantity</th>
                        <th class="p-2">Location</th>
                        <th class="p-2 rounded-r-lg">Status</th>
                    </tr>
                </thead>
                <tbody>
    <?php
 
    $conn = new mysqli("localhost", "root", "", "capstone");

 
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT farmer_name, month_of_harvest, possible_harvest, quantity, location, status FROM harvests";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr class='border-b'>
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
        echo "<tr><td colspan='6' class='p-2 text-center'>No Data Available</td></tr>";
    }

    $conn->close();
    ?>
</tbody>

            </table>
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