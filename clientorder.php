<?php
// Start the session if it's not already started
session_start();

// Function to display profile image
function displayProfileImage($profile_pic) {
    if ($profile_pic) {
        // Check if profile_pic is a file path (starts with "images/")
        if (is_string($profile_pic) && (strpos($profile_pic, 'images/') === 0 || strpos($profile_pic, 'profile.jpg') === 0)) {
            // Return the path directly
            return $profile_pic;
        } else {
            // Handle as binary data (old method)
            $base64Image = base64_encode($profile_pic);
            return "data:image/jpeg;base64,$base64Image";
        }
    } else {
        return "profile.jpg";
    }
}

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
    $sql = "SELECT client_ID, full_name, email, profile_pic FROM client_acc WHERE username = ?";
} else {
    $sql = "SELECT client_ID, full_name, email, profile_pic FROM client_acc WHERE email = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login_identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $client_ID = $user_data['client_ID'];
    $full_name = $user_data['full_name'];
    $email = $user_data['email'];
    $profile_pic = $user_data['profile_pic'];
    // Setting a default user type
    $user_type = "Client"; 
} else {
    // Handle case where user data is not found
    $client_ID = null;
    $full_name = "User";
    $email = $login_identifier; // Show the login identifier as email if full name not found
    $profile_pic = null;
    $user_type = "User";
}

$stmt->close();

// Default product type if no farmer is selected
$product_type = "Pineapple Fruit";

// Set default farmer data
$farmer_data = [
    'full_name' => 'Juan Dela Cruz',
    'age' => 45,
    'contact_number' => '+63 9123456789',
    'email' => 'juandelacruz@example.com',
    'farm_location' => 'Daet, Camarines Norte',
    'plantation_size' => '2.5 hectares',
    'flowering_date' => '2025-01-15',
    'possible_harvest' => 'Pineapple Fruit' // Default product type
];

// If a farmer name is provided in the URL, fetch the farmer's details from the harvests table
if (isset($_GET['farmer'])) {
    $farmer_name = $_GET['farmer'];
    
    // Query to get farmer data directly from the harvests table
    $harvest_query = "SELECT * FROM harvests WHERE farmer_name = ? LIMIT 1";
    $stmt = $conn->prepare($harvest_query);
    $stmt->bind_param("s", $farmer_name);
    $stmt->execute();
    $harvest_result = $stmt->get_result();
    
    if ($harvest_result && $harvest_result->num_rows > 0) {
        $harvest_data = $harvest_result->fetch_assoc();
        
        // Update farmer data with values from the harvests table
        $farmer_data = [
            'full_name' => $harvest_data['farmer_name'],
            'age' => 45, // Default value since it's not in harvests table
            'contact_number' => '+63 9123456789', // Default value
            'email' => 'farmer@example.com', // Default value
            'farm_location' => $harvest_data['location'] ?? 'Daet, Camarines Norte',
            'plantation_size' => '2.5 hectares', // Default value
            'flowering_date' => date('Y-m-d', strtotime('first day of ' . $harvest_data['month_of_harvest'] . ' 2025')),
            'possible_harvest' => $harvest_data['possible_harvest'] ?? 'Pineapple Fruit' // Get the product type
        ];
        
        // Update the product type for the order form
        $product_type = $farmer_data['possible_harvest'];
    }
    $stmt->close();
}

// Get product variants based on product type
function getProductVariants($product_type) {
    switch ($product_type) {
        case 'Pineapple Fruit':
            return ['Queen Pineapple', 'Formosa Pineapple'];
        case 'Pineapple Juice':
            return ['Pure Juice', 'With Pulp', 'Concentrated'];
        case 'Pineapple Fiber':
            return ['Raw Fiber', 'Processed Fiber', 'Dyed Fiber'];
        default:
            return ['Standard'];
    }
}

// Get product unit based on product type
function getProductUnit($product_type) {
    switch ($product_type) {
        case 'Pineapple Fruit':
            return 'piece(s)';
        case 'Pineapple Juice':
            return 'liter(s)';
        case 'Pineapple Fiber':
            return 'yard(s)';
        default:
            return 'unit(s)';
    }
}

// Get the variants for the selected product
$product_variants = getProductVariants($product_type);
$product_unit = getProductUnit($product_type);

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
            // Toggle logout modal
            $('#logoutButton').click(function() {
                $('#logoutModal').toggleClass('hidden');
            });
            
            $('#cancelLogout').click(function() {
                $('#logoutModal').addClass('hidden');
            });
            
            $('#confirmLogout').click(function() {
                window.location.href = 'account.php';
            });
            
            // Close modal if clicked outside
            $(window).click(function(event) {
                if ($(event.target).is('#logoutModal')) {
                    $('#logoutModal').addClass('hidden');
                }
            });
            
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
            
            // Update price and total when quantity changes
            $('#quantity').on('input', function() {
                updateTotal();
            });
            
            function updateTotal() {
                var quantity = parseInt($('#quantity').val()) || 0;
                var price = 50; // Base price, this could be dynamic based on product
                var total = quantity * price;
                
                // Update the cart table
                $('#cartQuantity').text(quantity);
                $('#cartPrice').text('₱' + price);
                $('#cartTotal').text('₱' + total);
                
                // Update the totals
                $('#subTotal').text('₱' + total);
                $('#grandTotal').text('₱' + total);
            }
        });
    </script>
</head>
<body class="min-h-screen bg-white">

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-80">
            <h3 class="text-lg font-bold mb-4">Confirm Logout</h3>
            <p class="mb-6">Are you sure you want to logout?</p>
            <div class="flex justify-end gap-4">
                <button id="cancelLogout" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                <button id="confirmLogout" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Logout</button>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <aside class="w-1/4 bg-[#115D5B] p-6 h-screen text-white fixed top-0 left-0 overflow-y-auto">
        <div class="flex flex-col items-center text-center">
            <img src="<?php echo displayProfileImage($profile_pic); ?>" alt="Profile" class="w-20 h-20 rounded-full border mb-2 object-cover">
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
                <li><a id="logoutButton" href="javascript:void(0)" class="block p-2 text-red-500 hover:text-red-700">Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content with proper margin to account for fixed sidebar -->
    <main class="ml-[25%] p-6 bg-white min-h-screen">
        <div class="grid grid-cols-2 gap-6">
            
            <!-- Farmer Details -->
            <div class="border p-4 rounded-lg">
                <h2 class="text-center font-bold text-lg mb-2">Farmer Details</h2>
                <div class="space-y-3">
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600">Farmer Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($farmer_data['full_name']); ?>" class="w-full border p-2 rounded" readonly>
                    </div>
                    
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600">Age</label>
                        <input type="text" value="<?php echo htmlspecialchars($farmer_data['age']); ?>" class="w-full border p-2 rounded" readonly>
                    </div>
                    
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600">Contact Number</label>
                        <input type="text" value="<?php echo htmlspecialchars($farmer_data['contact_number']); ?>" class="w-full border p-2 rounded" readonly>
                    </div>
                    
                    <?php if (!empty($farmer_data['email'])): ?>
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600">Email (Optional)</label>
                        <input type="text" value="<?php echo htmlspecialchars($farmer_data['email']); ?>" class="w-full border p-2 rounded" readonly>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600">Farm Location</label>
                        <input type="text" value="<?php echo htmlspecialchars($farmer_data['farm_location']); ?>" class="w-full border p-2 rounded" readonly>
                    </div>
                    
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600">Plantation Size</label>
                        <input type="text" value="<?php echo htmlspecialchars($farmer_data['plantation_size']); ?>" class="w-full border p-2 rounded" readonly>
                    </div>
                    
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600">Flowering Date</label>
                        <input type="text" value="<?php echo htmlspecialchars($farmer_data['flowering_date']); ?>" class="w-full border p-2 rounded" readonly>
                    </div>
                    
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600">Available Product</label>
                        <input type="text" value="<?php echo htmlspecialchars($farmer_data['possible_harvest']); ?>" class="w-full border p-2 rounded bg-green-50" readonly>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="text-sm text-gray-600">Farm Location Map</label>
                    <div class="bg-gray-200 h-48 rounded flex items-center justify-center">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d124511.96928214153!2d122.90943525!3d14.102620349999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d741f7ac3635%3A0x39a4ab3c1a2dba03!2sDaet%2C%20Camarines%20Norte!5e0!3m2!1sen!2sph!4v1712647281695!5m2!1sen!2sph" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="border p-4 rounded-lg">
                <h2 class="font-bold text-green-700 text-xl"><?php echo htmlspecialchars($product_type); ?></h2>
                <p class="text-gray-600 text-sm mb-2">Please specify the details for your order</p>
                
                <input type="number" id="quantity" placeholder="Quantity (<?php echo htmlspecialchars($product_unit); ?>)" class="w-full border p-2 rounded mt-2">
                
                <select class="w-full border p-2 rounded mt-2">
                    <option value="">Mode of Payment</option>
                    <option value="COD">Cash on Delivery</option>
                    <option value="Bank">Bank Transfer</option>
                    <option value="Ewallet">E-wallet</option>
                </select>
                
                <select class="w-full border p-2 rounded mt-2">
                    <option value="">Variant</option>
                    <?php foreach ($product_variants as $variant): ?>
                        <option value="<?php echo htmlspecialchars($variant); ?>"><?php echo htmlspecialchars($variant); ?></option>
                    <?php endforeach; ?>
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
                        <td class="p-2"><?php echo htmlspecialchars($product_type); ?></td>
                        <td class="p-2">Not Selected</td>
                        <td class="p-2" id="cartQuantity">0</td>
                        <td class="p-2" id="cartPrice">₱50</td>
                        <td class="p-2" id="cartTotal">₱0</td>
                    </tr>
                </tbody>
            </table>

            <div class="text-right mt-4">
                <p>Sub Total: <span id="subTotal">₱0</span></p>
                <p>Shipping: ₱0.00</p>
                <p class="font-bold">Grand Total: <span id="grandTotal">₱0</span></p>
            </div>

            <div class="flex justify-end gap-4 mt-4">
                <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Cancel</button>
                <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Confirm</button>
            </div>
        </div>
    </main>

</body>
</html>