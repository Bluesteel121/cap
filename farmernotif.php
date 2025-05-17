<?php
// Include the shared database connection and functions
require_once 'db_connect.php';

// Get farmer data using the shared function
$farmer_data = getFarmerData($conn);
$login_identifier = isset($_SESSION['username']) ? $_SESSION['username'] : 
                   (isset($_SESSION['email']) ? $_SESSION['email'] : 
                   (isset($_SESSION['contact_num']) ? $_SESSION['contact_num'] : ""));

// Get the login field name
$login_field = "";
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    $login_field = "username";
} else if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
    $login_field = "email";
} else if (isset($_SESSION['contact_num']) && !empty($_SESSION['contact_num'])) {
    $login_field = "contact_num";
}

// Get the profile image source
$profileImageSrc = displayProfileImage($farmer_data['profile_picture']);

// Fetch notifications/orders from database
// This is a placeholder - replace with actual database query
$notifications = [];
// Example query: $notifications = getNotifications($conn, $farmer_data['id']);

// For demonstration purposes, we'll create sample notifications based on the images
$notifications = [
    [
        'id' => 1,
        'order_id' => 'O001',
        'customer_name' => 'John Khent A. Baile',
        'message' => 'How long does it take to deliver it?',
        'date' => '2025-05-01 14:30:00',
        'is_read' => false,
        'order_details' => [
            'item' => 'Pineapple',
            'variant' => 'Queen\'s pineapple',
            'quantity' => 1,
            'price' => 500,
            'shipping' => 0,
            'tax' => 0,
            'total' => 500,
            'payment_method' => 'COD',
            'shipping_address' => [
                'name' => 'John Khent A. Baile',
                'address' => 'Purok 2 Daisy Street',
                'city' => 'San Roque',
                'province' => 'Mercedes Camarines Norte',
                'contact' => '+63 9663902440'
            ],
            'billing_address' => [
                'name' => 'John Khent A. Baile',
                'address' => 'Purok 2 Daisy Street',
                'city' => 'San Roque',
                'province' => 'Mercedes Camarines Norte',
                'contact' => '+63 9663902440'
            ]
        ]
    ],
    [
        'id' => 2,
        'order_id' => 'O002',
        'customer_name' => 'Maria Santos',
        'message' => 'Do you offer discounts for bulk orders?',
        'date' => '2025-04-30 10:15:00',
        'is_read' => true,
        'order_details' => [
            'item' => 'Pineapple',
            'variant' => 'Formosa pineapple',
            'quantity' => 5,
            'price' => 2000,
            'shipping' => 100,
            'tax' => 0,
            'total' => 2100,
            'payment_method' => 'GCash',
            'shipping_address' => [
                'name' => 'Maria Santos',
                'address' => 'Block 4 Lot 12 Sampaguita St.',
                'city' => 'Daet',
                'province' => 'Camarines Norte',
                'contact' => '+63 9551234567'
            ],
            'billing_address' => [
                'name' => 'Maria Santos',
                'address' => 'Block 4 Lot 12 Sampaguita St.',
                'city' => 'Daet',
                'province' => 'Camarines Norte',
                'contact' => '+63 9551234567'
            ]
        ]
    ],
    [
        'id' => 3,
        'order_id' => 'O003',
        'customer_name' => 'Roberto Cruz',
        'message' => 'When will my order be shipped?',
        'date' => '2025-04-29 16:45:00',
        'is_read' => true,
        'order_details' => null
    ]
];

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_submit'])) {
    $notification_id = $_POST['notification_id'] ?? '';
    $reply_message = $_POST['reply_message'] ?? '';
    
    if (!empty($notification_id) && !empty($reply_message)) {
        // Here you would save the reply to your database
        // saveReply($conn, $notification_id, $farmer_data['id'], $reply_message);
        
        // Redirect to prevent form resubmission
        header("Location: notifications.php?message=reply_sent");
        exit;
    }
}

// Check for messages from redirects
$update_message = '';
if (isset($_GET['message'])) {
    if ($_GET['message'] === 'reply_sent') {
        $update_message = "Your reply has been sent successfully.";
    }
}

// Mark notification as read
if (isset($_GET['mark_read']) && !empty($_GET['mark_read'])) {
    $notification_id = $_GET['mark_read'];
    // markNotificationAsRead($conn, $notification_id, $farmer_data['id']);
    
    // Redirect to prevent processing again
    header("Location: notifications.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Pineapple Crops</title>
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
        function toggleOrderDetails(id) {
            const detailsElement = document.getElementById('order-details-' + id);
            const btnText = document.getElementById('btn-text-' + id);
            const btnIcon = document.getElementById('btn-icon-' + id);
            
            if (detailsElement.classList.contains('hidden')) {
                detailsElement.classList.remove('hidden');
                btnText.textContent = 'Hide Details';
                btnIcon.classList.remove('rotate-0');
                btnIcon.classList.add('rotate-180');
            } else {
                detailsElement.classList.add('hidden');
                btnText.textContent = 'Show More';
                btnIcon.classList.remove('rotate-180');
                btnIcon.classList.add('rotate-0');
            }
        }
        function openReplyForm(id) {
            const replyForm = document.getElementById('reply-form-' + id);
            if (replyForm.classList.contains('hidden')) {
                replyForm.classList.remove('hidden');
            } else {
                replyForm.classList.add('hidden');
            }
        }
    </script>
</head>
<body class="bg-green-50 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-1/4 bg-[#115D5B] p-6 h-screen fixed top-0 left-0 flex flex-col justify-between text-white">
            <div>
                <div class="flex flex-col items-center text-center">
                    <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile" class="w-20 h-20 rounded-full border mb-2 object-cover bg-white">
                    <h2 class="font-bold"><?php echo htmlspecialchars($farmer_data['name']); ?></h2>
                    <p class="text-sm"><?php echo htmlspecialchars($farmer_data['contact_num']); ?></p>
                    <p class="text-sm italic">Farmer</p>
                    <?php if(isset($farmer_data['status'])): ?>
                        <p class="text-xs mt-1 px-2 py-1 rounded-full <?= $farmer_data['status'] == 'Active' ? 'bg-green-600' : 'bg-red-600' ?>">
                            <?= htmlspecialchars($farmer_data['status']) ?>
                        </p>
                    <?php endif; ?>
                </div>
                <nav class="mt-6">
                    <ul class="space-y-2">
                        <li><a href="farmerpage.php" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Home</a></li>
                            
                        <li><a href="farmerprofile.php" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile</a></li>

                        <li><a href="farmer_request.php" class="flex items-center p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Request</a></li>

                        <li><a href="notifications.php" class="flex items-center p-2 bg-[#CAEED5] text-green-700 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            Notifications<?php 
                            $unreadCount = array_reduce($notifications, function($carry, $item) {
                                return $carry + ($item['is_read'] ? 0 : 1);
                            }, 0);
                            if ($unreadCount > 0): ?>
                                <span class="ml-2 bg-red-500 text-white text-xs rounded-full px-2 py-1"><?= $unreadCount ?></span>
                            <?php endif; ?>
                        </a></li>

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

        <!-- Main Content -->
        <main class="w-3/4 p-6 ml-[25%]">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-green-800">Notifications & Orders</h1>
                <a href="farmerpage.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Back to Dashboard</a>
            </header>
            
            <?php if (!empty($update_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <p><?= htmlspecialchars($update_message) ?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($notifications)): ?>
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <p class="mt-4 text-lg">You don't have any notifications yet.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="bg-white rounded-lg shadow overflow-hidden border-l-4 <?= $notification['is_read'] ? 'border-gray-300' : 'border-green-500' ?>">
                            <div class="p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h2 class="text-lg font-semibold flex items-center">
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($notification['customer_name']) ?>
                                            <span class="text-sm font-normal text-gray-500 ml-2">Order #<?= htmlspecialchars($notification['order_id']) ?></span>
                                        </h2>
                                        <p class="text-sm text-gray-500 mt-1"><?= date('F j, Y g:i A', strtotime($notification['date'])) ?></p>
                                    </div>
                                    <?php if (!$notification['is_read']): ?>
                                        <a href="?mark_read=<?= $notification['id'] ?>" class="text-sm text-blue-600 hover:text-blue-800">Mark as read</a>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mr-3">
                                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium"><?= htmlspecialchars($notification['message']) ?></p>
                                            <div class="mt-2">
                                                <button onclick="openReplyForm(<?= $notification['id'] ?>)" class="text-sm bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition mr-2">
                                                    Reply to Message
                                                </button>
                                            </div>
                                            <!-- Reply Form -->
                                            <div id="reply-form-<?= $notification['id'] ?>" class="mt-3 hidden">
                                                <form method="POST" action="notifications.php">
                                                    <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                                    <textarea name="reply_message" class="w-full p-2 border border-gray-300 rounded-lg focus:ring focus:ring-green-200 focus:border-green-500" rows="2" placeholder="Type your reply here..." required></textarea>
                                                    <div class="mt-2 flex justify-end">
                                                        <button type="submit" name="reply_submit" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700 transition">Send Reply</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($notification['order_details']): ?>
                                    <div class="mt-4">
                                        <button 
                                            onclick="toggleOrderDetails(<?= $notification['id'] ?>)" 
                                            class="flex items-center justify-center w-full py-2 bg-gray-100 hover:bg-gray-200 rounded-md transition"
                                        >
                                            <span id="btn-text-<?= $notification['id'] ?>">Show More</span>
                                            <svg id="btn-icon-<?= $notification['id'] ?>" class="w-4 h-4 ml-2 transform rotate-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <div id="order-details-<?= $notification['id'] ?>" class="mt-4 hidden">
                                            <div class="border rounded-lg overflow-hidden">
                                                <div class="bg-gray-50 p-3 border-b">
                                                    <h3 class="font-semibold text-center text-green-700">Order Details</h3>
                                                </div>
                                                <div class="p-4">
                                                    <table class="w-full">
                                                        <tr>
                                                            <td class="py-2 text-gray-600">Order ID:</td>
                                                            <td class="py-2 font-medium"><?= htmlspecialchars($notification['order_id']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="py-2 text-gray-600">Cart Items:</td>
                                                            <td class="py-2 font-medium"><?= htmlspecialchars($notification['order_details']['item']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="py-2 text-gray-600">Variant:</td>
                                                            <td class="py-2"><?= htmlspecialchars($notification['order_details']['variant']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="py-2 text-gray-600">Quantity:</td>
                                                            <td class="py-2"><?= htmlspecialchars($notification['order_details']['quantity']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="py-2 text-gray-600">Price:</td>
                                                            <td class="py-2">₱<?= number_format($notification['order_details']['price'], 2) ?></td>
                                                        </tr>
                                                    </table>
                                                    
                                                    <div class="mt-4 flex flex-col sm:flex-row gap-4">
                                                        <div class="flex-1">
                                                            <h4 class="font-medium text-green-700 border-b pb-2 mb-2">Shipping Address</h4>
                                                            <p><?= htmlspecialchars($notification['order_details']['shipping_address']['name']) ?></p>
                                                            <p><?= htmlspecialchars($notification['order_details']['shipping_address']['address']) ?></p>
                                                            <p><?= htmlspecialchars($notification['order_details']['shipping_address']['city']) ?></p>
                                                            <p><?= htmlspecialchars($notification['order_details']['shipping_address']['province']) ?></p>
                                                            <p><?= htmlspecialchars($notification['order_details']['shipping_address']['contact']) ?></p>
                                                        </div>
                                                        <div class="flex-1">
                                                            <h4 class="font-medium text-green-700 border-b pb-2 mb-2">Billing Address</h4>
                                                            <p><?= htmlspecialchars($notification['order_details']['billing_address']['name']) ?></p>
                                                            <p><?= htmlspecialchars($notification['order_details']['billing_address']['address']) ?></p>
                                                            <p><?= htmlspecialchars($notification['order_details']['billing_address']['city']) ?></p>
                                                            <p><?= htmlspecialchars($notification['order_details']['billing_address']['province']) ?></p>
                                                            <p><?= htmlspecialchars($notification['order_details']['billing_address']['contact']) ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-4 border-t pt-4">
                                                        <table class="w-full">
                                                            <tr>
                                                                <td class="py-1 text-right pr-4">Shipping:</td>
                                                                <td class="py-1 font-medium text-right">₱<?= number_format($notification['order_details']['shipping'], 2) ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="py-1 text-right pr-4">Tax:</td>
                                                                <td class="py-1 font-medium text-right">₱<?= number_format($notification['order_details']['tax'], 2) ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="py-1 text-right pr-4 font-semibold">Grand Total:</td>
                                                                <td class="py-1 font-bold text-right text-green-700">₱<?= number_format($notification['order_details']['total'], 2) ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="py-1 text-right pr-4">Payment Method:</td>
                                                                <td class="py-1 font-medium text-right"><?= htmlspecialchars($notification['order_details']['payment_method']) ?></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4">Confirm Logout</h3>
            <p class="mb-6">Are you sure you want to logout from your account?</p>
            <div class="flex justify-end space-x-4">
                <button onclick="closeLogoutModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-colors">Cancel</button>
                <button onclick="confirmLogout()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">Logout</button>
            </div>
        </div>
    </div>
</body>
</html>