




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CNLRRS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/YOUR_FONT_AWESOME_KIT.js" crossorigin="anonymous"></script>
</head>
<body class="h-screen flex relative">

    <!-- Back to Home Button -->
    <a href="index.php" class="absolute top-4 left-4 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg shadow-md hover:bg-gray-300">
        ← Back to Home
    </a>

    <!-- Left Section (User Role Selection) -->
    <div class="w-1/2 bg-[#115D5B] text-white flex flex-col justify-center items-center p-8">
        <h1 class="text-2xl font-bold mb-6">Welcome to CNLRRS</h1>
        <div class="flex space-x-6">
            <!-- Admin Button -->
            <button onclick="redirectToLogin('admin')" class="flex flex-col items-center">
                <div class="bg-white w-[150.61px] h-[140px] p-4 rounded-lg shadow-lg flex flex-col items-center justify-center">
                    <img src="Images/adminlogo.png" alt="Logo 1" class="h-20">
                    <i class="fas fa-user-cog text-[#115D5B] text-4xl"></i>
                </div>
                <p class="mt-2 text-lg font-semibold">Admin</p>
            </button>

            <!-- Farmer Button -->
            <button onclick="redirectToLogin('farmer')" class="flex flex-col items-center">
                <div class="bg-white w-[150.61px] h-[140px] p-4 rounded-lg shadow-lg flex flex-col items-center justify-center">
                    <img src="Images/farmerlogo.png" alt="Logo 1" class="h-20">
                    <i class="fas fa-users text-[#115D5B] text-4xl"></i>
                </div>
                <p class="mt-2 text-lg font-semibold">Farmers</p>
            </button>

            <!-- Client Button -->
            <button onclick="redirectToLogin('client')" class="flex flex-col items-center">
                <div class="bg-white w-[150.61px] h-[140px] p-4 rounded-lg shadow-lg flex flex-col items-center justify-center">
                    <img src="Images/customer.png" alt="Logo 1" class="h-20">
                    <i class="fas fa-handshake text-[#115D5B] text-4xl"></i>
                </div>
                <p class="mt-2 text-lg font-semibold">Client</p>
            </button>
        </div>
    </div>

    <!-- Right Section (Contact & Info) -->
    <div class="w-1/2 bg-[#1E3A34] text-white flex flex-col justify-center p-8">
        <div class="flex justify-center space-x-4 mb-4">
            <img src="Images/logo1.png"  alt="Logo 1" class="h-40">
            <img src="Images/logo2.png" alt="Logo 1" class="h-40">
        </div>
            <h2 class="text-white font-bold text-xl mt-4">DEPARTMENT OF AGRICULTURE RFO 5</h2>
            <h3 class="text-whhite font-bold text-lg">CAMARINES NORTE LOWLAND RAINFED RESEARCH STATION</h3>
            <div class="mt-6 text-white text-center">
                <p class="flex items-center justify-center gap-2">
                    <span>&#128205;</span> Calasgasan, Daet, Camarines Norte
                </p>
                <p class="flex items-center justify-center gap-2 mt-2">
                    <span>&#128231;</span> dacnlrrs@gmail.com
                </p>
                <p class="flex items-center justify-center gap-2 mt-2">
                    <span>&#128100;</span> Engr. Bella B. Frias<br>
                    <span class="text-sm">Superintendent/Agricultural Center Chief III</span>
                </p>
            </div>
    </div>

    <!-- JavaScript to Redirect Users -->
    <script>
        function redirectToLogin(role) {
            if (role === "admin") {
                window.location.href = "adminlogin.php"; // Replace with your admin login page
            } else if (role === "farmer") {
                window.location.href = "farmerlogin.php"; // Replace with your farmer login page
            } else if (role === "client") {
                window.location.href = "clientlogin.php"; // Replace with your client login page
            }
        }
    </script>

</body>
</html>
