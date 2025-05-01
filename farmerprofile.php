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

// Include GitHub upload function if you have it
if (file_exists('github_upload.php')) {
    require_once 'github_upload.php';
}

// Get the profile image source
$profileImageSrc = displayProfileImage($farmer_data['profile_picture']);

// Process form submission
$update_message = "";
$update_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = $_POST['name'];
    $birthdate = $_POST['birthdate'];
    $sex = $_POST['sex'];
    $email = $_POST['email'];
    $civil_status = $_POST['civil_status'];
    $username = $_POST['username'];
    $contact_num = $_POST['contact_num'];
    $address = $_POST['address'];
    $farm_location = $_POST['farm_location'];
    $varieties = $_POST['varieties'];
    $farm_size = $_POST['farm_size'];
    $yield = $_POST['yield'];
    $years_farming = $_POST['years_farming'];
    $market = $_POST['market'];
    $soil_type = $_POST['soil_type'];
    $fertilizer = $_POST['fertilizer'];
    $new_password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : null;
    
    // Validate password if provided
    if ($new_password !== null) {
        if ($new_password !== $confirm_password) {
            $update_error = "Passwords do not match!";
        }
    }
    
    // Proceed with update if no errors
    if (empty($update_error)) {
        // Start building the SQL query
        $sql_parts = [];
        $param_types = "";
        $param_values = [];
        
        // Add fields to update
        $sql_parts[] = "name = ?";
        $param_types .= "s";
        $param_values[] = $name;
        
        $sql_parts[] = "birthdate = ?";
        $param_types .= "s";
        $param_values[] = $birthdate;
        
        $sql_parts[] = "sex = ?";
        $param_types .= "s";
        $param_values[] = $sex;
        
        $sql_parts[] = "email = ?";
        $param_types .= "s";
        $param_values[] = $email;
        
        $sql_parts[] = "civil_status = ?";
        $param_types .= "s";
        $param_values[] = $civil_status;
        
        $sql_parts[] = "username = ?";
        $param_types .= "s";
        $param_values[] = $username;
        
        $sql_parts[] = "contact_num = ?"; 
        $param_types .= "s";
        $param_values[] = $contact_num;
        
        $sql_parts[] = "address = ?";
        $param_types .= "s";
        $param_values[] = $address;
        
        $sql_parts[] = "farm_location = ?";
        $param_types .= "s";
        $param_values[] = $farm_location;
        
        $sql_parts[] = "varieties = ?";
        $param_types .= "s";
        $param_values[] = $varieties;
        
        $sql_parts[] = "farm_size = ?";
        $param_types .= "s";
        $param_values[] = $farm_size;
        
        $sql_parts[] = "yield = ?";
        $param_types .= "s";
        $param_values[] = $yield;
        
        $sql_parts[] = "years_farming = ?";
        $param_types .= "s";
        $param_values[] = $years_farming;
        
        $sql_parts[] = "market = ?";
        $param_types .= "s";
        $param_values[] = $market;
        
        $sql_parts[] = "soil_type = ?";
        $param_types .= "s";
        $param_values[] = $soil_type;
        
        $sql_parts[] = "fertilizer = ?";
        $param_types .= "s";
        $param_values[] = $fertilizer;
        
        // Add password if it was provided - use plain text password
        if ($new_password !== null) {
            $sql_parts[] = "password = ?";
            $param_types .= "s";
            $param_values[] = $new_password; // Use plain text password instead of hashed
        }
        
        // Process image upload if a file was provided
        if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["size"] > 0) {
            $file = $_FILES["profile_pic"];
            
            // Check if there's an error with the uploaded file
            if ($file["error"] == 0) {
                // Check file size (max 2MB)
                if ($file["size"] <= 2000000) {
                    // Get file extension
                    $file_ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
                    $allowed_exts = array("jpg", "jpeg", "png", "gif");
                    
                    if (in_array($file_ext, $allowed_exts)) {
                        // Create unique filename
                        $new_filename = "farmer_profile_" . $username . "_" . time() . "." . $file_ext;
                        $local_upload_dir = "images/";
                        $github_upload_dir = "images/";
                        
                        // Create local directory if it doesn't exist
                        if (!file_exists($local_upload_dir)) {
                            mkdir($local_upload_dir, 0777, true);
                        }
                        
                        $local_path = $local_upload_dir . $new_filename;
                        $github_path = $github_upload_dir . $new_filename;
                        
                        // Move the uploaded file to the local images directory
                        if (move_uploaded_file($file["tmp_name"], $local_path)) {
                            // Try to upload to GitHub if function exists
                            if (function_exists('uploadToGitHub')) {
                                $github_result = uploadToGitHub($local_path, $github_path);
                                
                                if ($github_result === true) {
                                    // GitHub upload successful
                                    // Add profile_pic path to SQL update
                                    $sql_parts[] = "profile_picture = ?";
                                    $param_types .= "s"; // string for file path
                                    $param_values[] = $github_path;
                                } else {
                                    // GitHub upload failed, but we still have the local file
                                    // Log the error but continue with the local path
                                    error_log("GitHub upload failed: " . $github_result);
                                    
                                    $sql_parts[] = "profile_picture = ?";
                                    $param_types .= "s";
                                    $param_values[] = $local_path;
                                    
                                    // Add a warning to the user
                                    $update_message = "Profile will be updated, but GitHub sync failed. ";
                                }
                            } else {
                                // No GitHub upload function, just use local path
                                $sql_parts[] = "profile_picture = ?";
                                $param_types .= "s";
                                $param_values[] = $local_path;
                            }
                        } else {
                            $update_error = "Failed to save the image.";
                        }
                    } else {
                        $update_error = "Only JPG, JPEG, PNG & GIF files are allowed.";
                    }
                } else {
                    $update_error = "File is too large. Maximum file size is 2MB.";
                }
            } else {
                $update_error = "Error uploading file. Error code: " . $file["error"];
            }
        }
        
        // If still no errors, proceed with update
        if (empty($update_error)) {
            // Complete the SQL query
            $sql = "UPDATE farmer_acc SET " . implode(", ", $sql_parts) . " WHERE ";
            
            // Use the farmer_id for the WHERE clause instead of login field
            $sql .= "farmer_id = ?";
            
            $param_types .= "i"; // integer for farmer_id
            $param_values[] = $farmer_data['farmer_id'];
            
            // Prepare the statement
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                // Create a reference array for bind_param
                $bind_params = array();
                $bind_params[] = $param_types;
                
                // Create references for the parameter values
                for ($i = 0; $i < count($param_values); $i++) {
                    $bind_params[] = &$param_values[$i];
                }
                
                // Call bind_param with the reference array
                call_user_func_array(array($stmt, 'bind_param'), $bind_params);
                
                if ($stmt->execute()) {
                    $update_message .= "Profile updated successfully!";
                    
                    // Update session variables to reflect changes
                    if ($username != $_SESSION['username'] && isset($_SESSION['username'])) {
                        $_SESSION['username'] = $username;
                    }
                    if ($email != $_SESSION['email'] && isset($_SESSION['email'])) {
                        $_SESSION['email'] = $email;
                    }
                    if ($contact_num != $_SESSION['contact_num'] && isset($_SESSION['contact_num'])) {
                        $_SESSION['contact_num'] = $contact_num;
                    }
                    
                    // Clear any cached data
                    if (isset($_SESSION['farmer_data'])) {
                        unset($_SESSION['farmer_data']);
                    }
                    
                    // Redirect to refresh the page with new data
                    header("Location: farmerprofile.php?message=" . urlencode($update_message));
                    exit();
                } else {
                    $update_error = "Error executing update: " . $stmt->error;
                }
                
                $stmt->close();
            } else {
                $update_error = "Error preparing statement: " . $conn->error;
            }
        }
    }
}

// Check for messages from redirects
if (isset($_GET['message'])) {
    $update_message = $_GET['message'];
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Profile - Pineapple Crops</title>
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
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('image-preview');
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
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
                        <li><a href="farmerpage.php" class="flex items-center  p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Home</a></li>
                            
                        <li><a href="farmerprofile.php" class="flex items-center  p-2 bg-[#CAEED5] text-green-700 rounded">
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
        <main class="w-3/4 p-6 ml-[25%]">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-green-800">Farmer Profile</h1>
                <a href="farmerpage.php" class="bg-blue-600 text-white px-4 py-2 rounded">Back to Dashboard</a>
            </header>
            
            <?php if (!empty($update_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $update_message; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($update_error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $update_error; ?></span>
                </div>
            <?php endif; ?>
            
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="space-y-6">
                    <!-- Profile Picture -->
                    <div class="flex flex-col items-center mb-6">
                        <h3 class="text-lg font-bold mb-2 text-green-800">Profile Picture</h3>
                        <img id="image-preview" src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile Preview" class="w-32 h-32 rounded-full object-cover border-2 border-green-700 mb-4 bg-white">
                        
                        <input type="file" name="profile_pic" id="profile_pic" accept="image/*" 
                               class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 
                                      file:rounded-full file:border-0 file:text-sm file:font-semibold
                                      file:bg-green-100 file:text-green-700 hover:file:bg-green-200" 
                               onchange="previewImage(event)">
                    </div>
                    
                    <!-- Personal Information Section -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4 text-green-800 border-b border-green-200 pb-2">Personal Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($farmer_data['name']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Birthdate -->
                            <div>
                                <label for="birthdate" class="block text-sm font-medium text-gray-700">Birthdate</label>
                                <input type="date" name="birthdate" id="birthdate" value="<?php echo htmlspecialchars($farmer_data['birthdate']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Sex -->
                            <div>
                                <label for="sex" class="block text-sm font-medium text-gray-700">Sex</label>
                                <select name="sex" id="sex" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                        bg-white text-gray-800 px-3 py-2">
                                    <option value="Male" <?php echo ($farmer_data['sex'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($farmer_data['sex'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($farmer_data['sex'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <!-- Civil Status -->
                            <div>
                                <label for="civil_status" class="block text-sm font-medium text-gray-700">Civil Status</label>
                                <select name="civil_status" id="civil_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                        bg-white text-gray-800 px-3 py-2">
                                    <option value="Single" <?php echo ($farmer_data['civil_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married" <?php echo ($farmer_data['civil_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                                    <option value="Widowed" <?php echo ($farmer_data['civil_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                                    <option value="Divorced" <?php echo ($farmer_data['civil_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Separated" <?php echo ($farmer_data['civil_status'] == 'Separated') ? 'selected' : ''; ?>>Separated</option>
                                </select>
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($farmer_data['email']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Username -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($farmer_data['username']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                                <input type="password" name="password" id="password" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Confirm Password -->
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Phone Number -->
                            <div>
                                <label for="contact_num" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="text" name="contact_num" id="contact_num" value="<?php echo htmlspecialchars($farmer_data['contact_num']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Address -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($farmer_data['address']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Farming Information Section -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4 text-green-800 border-b border-green-200 pb-2">Farming Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Farm Location -->
                            <div>
                                <label for="farm_location" class="block text-sm font-medium text-gray-700">Farm Location</label>
                                <input type="text" name="farm_location" id="farm_location" value="<?php echo htmlspecialchars($farmer_data['farm_location']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Varieties -->
                            <div>
                                <label for="varieties" class="block text-sm font-medium text-gray-700">Pineapple Varieties</label>
                                <input type="text" name="varieties" id="varieties" value="<?php echo htmlspecialchars($farmer_data['varieties']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Farm Size -->
                            <div>
                                <label for="farm_size" class="block text-sm font-medium text-gray-700">Farm Size (hectares)</label>
                                <input type="text" name="farm_size" id="farm_size" value="<?php echo htmlspecialchars($farmer_data['farm_size']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Yield -->
                            <div>
                                <label for="yield" class="block text-sm font-medium text-gray-700">Yield (tons/hectare)</label>
                                <input type="text" name="yield" id="yield" value="<?php echo htmlspecialchars($farmer_data['yield']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Years Farming -->
                            <div>
                                <label for="years_farming" class="block text-sm font-medium text-gray-700">Years of Farming Experience</label>
                                <input type="text" name="years_farming" id="years_farming" value="<?php echo htmlspecialchars($farmer_data['years_farming']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Market -->
                            <div>
                                <label for="market" class="block text-sm font-medium text-gray-700">Market/Buyers</label>
                                <input type="text" name="market" id="market" value="<?php echo htmlspecialchars($farmer_data['market']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Soil Type -->
                            <div>
                                <label for="soil_type" class="block text-sm font-medium text-gray-700">Soil Type</label>
                                <select name="soil_type" id="soil_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                        bg-white text-gray-800 px-3 py-2">
                                    <option value="Sandy" <?php echo ($farmer_data['soil_type'] == 'Sandy') ? 'selected' : ''; ?>>Sandy</option>
                                    <option value="Clay" <?php echo ($farmer_data['soil_type'] == 'Clay') ? 'selected' : ''; ?>>Clay</option>
                                    <option value="Loamy" <?php echo ($farmer_data['soil_type'] == 'Loamy') ? 'selected' : ''; ?>>Loamy</option>
                                    <option value="Silt" <?php echo ($farmer_data['soil_type'] == 'Silt') ? 'selected' : ''; ?>>Silt</option>
                                    <option value="Peat" <?php echo ($farmer_data['soil_type'] == 'Peat') ? 'selected' : ''; ?>>Peat</option>
                                    <option value="Chalk" <?php echo ($farmer_data['soil_type'] == 'Chalk') ? 'selected' : ''; ?>>Chalk</option>
                                    <option value="Other" <?php echo ($farmer_data['soil_type'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <!-- Fertilizer -->
                            <div>
                                <label for="fertilizer" class="block text-sm font-medium text-gray-700">Fertilizer Used</label>
                                <input type="text" name="fertilizer" id="fertilizer" value="<?php echo htmlspecialchars($farmer_data['fertilizer']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                              bg-white text-gray-800 px-3 py-2">
                            </div>
                            
                            <!-- Flowering Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Flowering Status</label>
                                <div class="mt-2">
                                    <span class="px-3 py-1 rounded-full text-sm <?php echo ($farmer_data['flowered'] == 1) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo ($farmer_data['flowered'] == 1) ? 'Flowered' : 'Not Flowered'; ?>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">This status is updated by the system</p>
                            </div>
                            
                            <!-- Pest Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pest Infestation Status</label>
                                <div class="mt-2">
                                    <span class="px-3 py-1 rounded-full text-sm <?php echo ($farmer_data['pested'] == 1) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo ($farmer_data['pested'] == 1) ? 'Detected' : 'No Pests Detected'; ?>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">This status is updated by the system</p>
                            </div>
                            
                            <!-- Account Created -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Account Created</label>
                                <div class="mt-2 text-sm text-gray-600">
                                    <?php echo date('F j, Y', strtotime($farmer_data['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="flex justify-center">
                        <button type="submit" class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-colors">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
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