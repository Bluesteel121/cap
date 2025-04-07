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

// Get all provinces from the location database, ordered alphabetically
$provinces_query = "SELECT DISTINCT province FROM location ORDER BY province ASC";
$provinces_result = $conn->query($provinces_query);
$provinces = [];
if ($provinces_result && $provinces_result->num_rows > 0) {
    while($row = $provinces_result->fetch_assoc()) {
        $provinces[] = $row['province'];
    }
}

// AJAX handlers for dynamic dropdowns
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'get_municipalities' && isset($_GET['province'])) {
        $province = $_GET['province'];
        $municipalities_query = "SELECT DISTINCT municipality FROM location WHERE province = ? ORDER BY municipality ASC";
        $stmt = $conn->prepare($municipalities_query);
        $stmt->bind_param("s", $province);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $municipalities = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $municipalities[] = $row['municipality'];
            }
        }
        
        echo json_encode($municipalities);
        exit;
    }
    
    if ($_GET['action'] == 'get_barangays' && isset($_GET['province']) && isset($_GET['municipality'])) {
        $province = $_GET['province'];
        $municipality = $_GET['municipality'];
        $barangays_query = "SELECT DISTINCT barangay FROM location WHERE province = ? AND municipality = ? ORDER BY barangay ASC";
        $stmt = $conn->prepare($barangays_query);
        $stmt->bind_param("ss", $province, $municipality);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $barangays = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $barangays[] = $row['barangay'];
            }
        }
        
        echo json_encode($barangays);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // When province selection changes
            $('#province').change(function() {
                var province = $(this).val();
                if (province) {
                    // Clear municipality and barangay dropdowns
                    $('#municipality').html('<option value="">Select Municipality</option>');
                    $('#barangay').html('<option value="">Select Barangay</option>');
                    
                    // AJAX request to get municipalities for selected province
                    $.ajax({
                        url: 'clientorder.php',
                        type: 'GET',
                        data: {
                            'action': 'get_municipalities',
                            'province': province
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.length > 0) {
                                // Populate municipality dropdown
                                $.each(data, function(key, value) {
                                    $('#municipality').append('<option value="' + value + '">' + value + '</option>');
                                });
                            }
                        }
                    });
                }
            });
            
            // When municipality selection changes
            $('#municipality').change(function() {
                var province = $('#province').val();
                var municipality = $(this).val();
                if (province && municipality) {
                    // Clear barangay dropdown
                    $('#barangay').html('<option value="">Select Barangay</option>');
                    
                    // AJAX request to get barangays for selected province and municipality
                    $.ajax({
                        url: 'clientorder.php',
                        type: 'GET',
                        data: {
                            'action': 'get_barangays',
                            'province': province,
                            'municipality': municipality
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.length > 0) {
                                // Populate barangay dropdown
                                $.each(data, function(key, value) {
                                    $('#barangay').append('<option value="' + value + '">' + value + '</option>');
                                });
                            }
                        }
                    });
                }
            });
        });
    </script>
</head>
<body class="min-h-screen bg-white">

    <!-- Sidebar -->
    <aside class="w-1/4 bg-[#115D5B] p-6 h-screen text-white fixed top-0 left-0 overflow-y-auto">
        <div class="flex flex-col items-center text-center">
            <img src="profile.jpg" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
            <h2 class="font-bold"><?php echo htmlspecialchars($full_name); ?></h2>
            <p class="text-sm"><?php echo htmlspecialchars($email); ?></p>
            <p class="text-sm italic"><?php echo htmlspecialchars($user_type); ?></p>
        </div>
        <nav class="mt-6">
            <ul class="space-y-2">
                <li><a href="clientpage.php" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Home</a></li>
                <li><a href="#" class="block p-2 bg-[#CAEED5] text-green-700 rounded">Order</a></li>
                <li><a href="#" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Notifications</a></li>
                <li><a href="clientprofile.php" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Profile</a></li>
                <li><a href="#" class="block p-2 text-red-500 hover:text-red-700">Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content with proper margin to account for fixed sidebar -->
    <main class="ml-[25%] p-6 bg-white min-h-screen">
        <div class="grid grid-cols-2 gap-6">
            
            <!-- Customer Details -->
            <div class="border p-4 rounded-lg">
                <h2 class="text-center font-bold">Customer Details</h2>
                <p class="text-center text-sm">HOME</p>
                <p class="text-center text-sm">Default Shipping Address</p>
                <button class="block w-full bg-gray-200 text-center py-2 rounded mt-2">+ Add Address</button>
                <input type="text" value="John Khent Avellana" class="w-full border p-2 rounded mt-2">
                <input type="text" value="+63 9663902440" class="w-full border p-2 rounded mt-2">
                <input type="text" value="JohnkhentAvellana77@gmail.com" class="w-full border p-2 rounded mt-2">
                <input type="text" value="Purok 2 Daisy Street Mercedes Camarines Norte" class="w-full border p-2 rounded mt-2">
                <img src="image.png" alt="Map" class="w-full mt-2 rounded">
            </div>

            <!-- Order Details -->
            <div class="border p-4 rounded-lg">
                <h2 class="font-bold">Pineapple Fruit</h2>
                <input type="number" placeholder="Quantity" class="w-full border p-2 rounded mt-2">
                <select class="w-full border p-2 rounded mt-2">
                    <option>Mode of Payment</option>
                </select>
                <select class="w-full border p-2 rounded mt-2">
                    <option>Variant</option>
                </select>

                <h2 class="font-bold mt-4">Customer Address</h2>
                <select id="province" class="w-full border p-2 rounded mt-2">
                    <option value="">Select Province</option>
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?php echo htmlspecialchars($province); ?>"><?php echo htmlspecialchars($province); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="municipality" class="w-full border p-2 rounded mt-2">
                    <option value="">Select Municipality</option>
                </select>
                <select id="barangay" class="w-full border p-2 rounded mt-2">
                    <option value="">Select Barangay</option>
                </select>
                <input type="text" placeholder="Purok/Zone/Street" class="w-full border p-2 rounded mt-2">
            </div>
        </div>

        <!-- Cart Items -->
        <div class="border p-4 rounded-lg mt-6">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#4CAF50] text-white">
                        <th class="p-2">Cart Items</th>
                        <th class="p-2">Variant</th>
                        <th class="p-2">Quantity</th>
                        <th class="p-2">Price</th>
                        <th class="p-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center">
                        <td class="p-2">Pineapple</td>
                        <td class="p-2">Queen Pineapple</td>
                        <td class="p-2">1</td>
                        <td class="p-2">₱500</td>
                        <td class="p-2">₱500</td>
                    </tr>
                </tbody>
            </table>

            <div class="text-right mt-4">
                <p>Sub Total: ₱500</p>
                <p>Shipping: ₱0.00</p>
                <p class="font-bold">Grand Total: ₱500</p>
            </div>

            <div class="flex justify-end gap-4 mt-4">
                <button class="bg-red-500 text-white px-4 py-2 rounded">Cancel</button>
                <button class="bg-green-500 text-white px-4 py-2 rounded">Confirm</button>
            </div>
        </div>
    </main>

</body>
</html>