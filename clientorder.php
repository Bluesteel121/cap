<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-white">

    <!-- Sidebar -->
    <aside class="w-1/4 bg-[#115D5B] p-6 h-screen text-white fixed top-0 left-0 overflow-y-auto">
        <div class="flex flex-col items-center text-center">
            <img src="profile.jpg" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
            <h2 class="font-bold">Ricardo Dela Cruz</h2>
            <p class="text-sm">pajcn@gmail.com</p>
            <p class="text-sm italic">Farmer</p>
        </div>
        <nav class="mt-6">
            <ul class="space-y-2">
                <li><a href="clientpage.php" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Home</a></li>
                <li><a href="#" class="block p-2 bg-[#CAEED5] text-green-700 rounded">Order</a></li>
                <li><a href="#" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Notifications</a></li>
                <li><a href="#" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Profile</a></li>
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
                <select class="w-full border p-2 rounded mt-2">
                    <option>Province</option>
                </select>
                <select class="w-full border p-2 rounded mt-2">
                    <option>Municipality</option>
                </select>
                <select class="w-full border p-2 rounded mt-2">
                    <option>Barangay</option>
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