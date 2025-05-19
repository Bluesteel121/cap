<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include GitHub upload function
require_once 'github_upload.php'; // Save the previous function in this file

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables with error handling
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    error_log("Error loading .env file: " . $e->getMessage());
    die("Configuration error. Please contact the administrator.");
}

// Debug - Log environment variables (redact sensitive info)
error_log("DB_HOST set: " . (isset($_ENV['DB_HOST']) ? 'Yes' : 'No'));
error_log("DB_USER set: " . (isset($_ENV['DB_USER']) ? 'Yes' : 'No'));
error_log("DB_NAME set: " . (isset($_ENV['DB_NAME']) ? 'Yes' : 'No'));

// Database connection with better error handling
try {
    $conn = new mysqli(
        $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        $_ENV['DB_NAME']
    );
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection error. Please try again later or contact support.");
}

// Debug - Log session variables
error_log("Session variables: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username']) && !isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    error_log("User not logged in, redirecting to account.php");
    header("Location: account.php");
    exit();
}

// Get the login identifier (either username or email)
$login_identifier = isset($_SESSION['username']) ? $_SESSION['username'] : $_SESSION['email'];
error_log("Login identifier: " . $login_identifier);

// Get user data from database using the login identifier
try {
    if (isset($_SESSION['username'])) {
        $sql = "SELECT full_name, email, username, phone_number, profile_pic FROM client_acc WHERE username = ?";
    } else {
        $sql = "SELECT full_name, email, username, phone_number, profile_pic FROM client_acc WHERE email = ?";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $login_identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    error_log("SQL Query executed: " . $sql . " with parameter: " . $login_identifier);
    error_log("Result rows: " . $result->num_rows);

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $full_name = $user_data['full_name'];
        $email = $user_data['email'];
        $username = $user_data['username'];
        $phone_number = $user_data['phone_number'];
        $profile_pic = $user_data['profile_pic'];
        // Setting a default user type
        $user_type = "Client"; 
        
        error_log("User data found: " . print_r($user_data, true));
    } else {
        // Handle case where user data is not found
        error_log("No user data found for " . $login_identifier);
        throw new Exception("User account not found. Please contact support.");
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Error retrieving user data: " . $e->getMessage());
    $full_name = "User";
    $email = $login_identifier ?? "Unknown";
    $username = "";
    $phone_number = "";
    $profile_pic = null;
    $user_type = "User";
}

// Function to display profile image
function displayProfileImage($profile_pic) {
    if ($profile_pic && strlen($profile_pic) > 0) {
        // Return the path to the image
        return $profile_pic;
    } else {
        // Return the path to the default profile image
        return "profile.jpg";
    }
}

// Get the profile image source
$profileImageSrc = displayProfileImage($profile_pic);

// Process form submission
$update_message = "";
$update_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Collect form data
        $new_full_name = $_POST['full_name'];
        $new_email = $_POST['email'];
        $new_username = $_POST['username'];
        $new_phone_number = $_POST['phone_number'];
        $new_password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : null;
        
        // Validate form data
        if (empty($new_full_name) || empty($new_email) || empty($new_username)) {
            throw new Exception("Name, email and username are required fields.");
        }
        
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }
        
        // Validate password if provided
        if ($new_password !== null) {
            if ($new_password !== $confirm_password) {
                throw new Exception("Passwords do not match!");
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception("Password must be at least 6 characters long.");
            }
        }
        
        // Start building the SQL query
        $sql_parts = [];
        $param_types = "";
        $param_values = [];
        
        // Add fields to update
        $sql_parts[] = "full_name = ?";
        $param_types .= "s";
        $param_values[] = $new_full_name;
        
        $sql_parts[] = "email = ?";
        $param_types .= "s";
        $param_values[] = $new_email;
        
        $sql_parts[] = "username = ?";
        $param_types .= "s";
        $param_values[] = $new_username;
        
        $sql_parts[] = "phone_number = ?";
        $param_types .= "s";
        $param_values[] = $new_phone_number;
        
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
                        $new_filename = "profile_" . $new_username . "_" . time() . "." . $file_ext;
                        $local_upload_dir = "images/"; // Change to a relative path that exists
                        $github_upload_dir = "images/";
                        
                        // Create local directory if it doesn't exist
                        if (!file_exists($local_upload_dir)) {
                            if (!mkdir($local_upload_dir, 0777, true)) {
                                error_log("Failed to create directory: " . $local_upload_dir);
                                throw new Exception("Failed to create upload directory.");
                            }
                        }
                        
                        $local_path = $local_upload_dir . $new_filename;
                        $github_path = $github_upload_dir . $new_filename;
                        
                        // Move the uploaded file to the local images directory
                        if (move_uploaded_file($file["tmp_name"], $local_path)) {
                            // Try to upload to GitHub
                            try {
                                $github_result = uploadToGitHub($local_path, $github_path);
                                
                                if ($github_result === true) {
                                    // GitHub upload successful
                                    // Add profile_pic path to SQL update
                                    $sql_parts[] = "profile_pic = ?";
                                    $param_types .= "s"; // string for file path
                                    $param_values[] = $github_path;
                                } else {
                                    // GitHub upload failed, but we still have the local file
                                    // Log the error but continue with the local path
                                    error_log("GitHub upload failed: " . $github_result);
                                    
                                    $sql_parts[] = "profile_pic = ?";
                                    $param_types .= "s";
                                    $param_values[] = $local_path;
                                    
                                    // Add a warning to the user
                                    $update_message = "Profile will be updated, but GitHub sync failed. ";
                                }
                            } catch (Exception $e) {
                                error_log("GitHub upload exception: " . $e->getMessage());
                                // Continue with local path
                                $sql_parts[] = "profile_pic = ?";
                                $param_types .= "s";
                                $param_values[] = $local_path;
                            }
                        } else {
                            throw new Exception("Failed to save the image. Check directory permissions.");
                        }
                    } else {
                        throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
                    }
                } else {
                    throw new Exception("File is too large. Maximum file size is 2MB.");
                }
            } else {
                throw new Exception("Error uploading file. Error code: " . $file["error"]);
            }
        }
        
        // Complete the SQL query
        $sql = "UPDATE client_acc SET " . implode(", ", $sql_parts) . " WHERE ";
        
        // Determine which field to use for the WHERE clause
        if (isset($_SESSION['username'])) {
            $sql .= "username = ?";
        } else {
            $sql .= "email = ?";
        }
        
        $param_types .= "s";
        $param_values[] = $login_identifier;
        
        // Debug - Log SQL
        error_log("Update SQL: " . $sql);
        error_log("Param types: " . $param_types);
        error_log("Param values: " . print_r($param_values, true));
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            // Create a reference array for binding parameters
            $bind_params = array();
            $bind_params[] = &$param_types;
            
            for ($i = 0; $i < count($param_values); $i++) {
                $bind_params[] = &$param_values[$i];
            }
            
            // Bind parameters dynamically
            call_user_func_array(array($stmt, 'bind_param'), $bind_params);
            
            // Execute the statement
            if ($stmt->execute()) {
                $update_message .= "Profile updated successfully!";
                
                // Update session variables if username or email changed
                if (isset($_SESSION['username']) && $new_username != $_SESSION['username']) {
                    $_SESSION['username'] = $new_username;
                }
                if (isset($_SESSION['email']) && $new_email != $_SESSION['email']) {
                    $_SESSION['email'] = $new_email;
                }
                
                // Redirect to refresh the page with new data
                header("Location: clientprofile.php?message=" . urlencode($update_message));
                $stmt->close();
                exit();
            } else {
                throw new Exception("Error executing update: " . $stmt->error);
            }
        } else {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
    } catch (Exception $e) {
        $update_error = $e->getMessage();
        error_log("Update error: " . $update_error);
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
    <title>Edit Profile - Pineapple Crops</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function openLogoutModal() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }
        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }
        function confirmLogout() {
            window.location.href = 'logout.php'; // Changed to logout.php
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
<body class="flex">
    <!-- Sidebar -->
    <aside class="w-1/4 bg-[#115D5B] p-6 h-screen flex flex-col justify-between text-white">
    <div>
        <div class="flex flex-col items-center text-center">
            <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile" class="w-20 h-20 rounded-full mb-2 object-cover bg-white">
            <h2 class="font-bold"><?php echo htmlspecialchars($full_name); ?></h2>
            <p class="text-sm"><?php echo htmlspecialchars($email); ?></p>
            <p class="text-sm italic"><?php echo htmlspecialchars($user_type); ?></p>
        </div>
        <nav class="mt-6 ">
    <ul class="space-y-2">
        <li><a href="clientpage.php" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Home</a></li>
        <li><a href="clientorder.php" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Order</a></li>
        <li><a href="#" class="block p-2 hover:bg-[#CAEED5] hover:text-green-700 rounded">Notifications</a></li>
        <li><a href="clientprofile.php" class="block p-2 bg-[#CAEED5] text-green-700 rounded hover:bg-gray-300">Profile</a></li>
        <li><a href="#" class="block p-2 text-red-500 hover:text-red-700" onclick="openLogoutModal()">Logout</a></li>
    </ul>
</nav>
        </div>
        <footer class="text-center text-xs">&copy; 2025 Camarines Norte Lowland Rainfed Research Station</footer>
    </aside>
    
    <!-- Main Content -->
    <main class="w-3/4 p-6 bg-white">
        <header class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-green-700">Edit Profile</h1>
            <a href="clientpage.php" class="bg-blue-600 text-white px-4 py-2 rounded">Back to Home</a>
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
        
        <div class="bg-[#115D5B] p-6 rounded-lg shadow-lg text-white">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="space-y-6">
                <!-- Profile Picture -->
                <div class="flex flex-col items-center mb-6">
                    <h3 class="text-lg font-bold mb-2">Profile Picture</h3>
                    <img id="image-preview" src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile Preview" class="w-32 h-32 rounded-full object-cover border-2 border-white mb-4 bg-white">
                    
                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*" 
                           class="block w-full text-sm text-white file:mr-4 file:py-2 file:px-4 
                                  file:rounded-full file:border-0 file:text-sm file:font-semibold
                                  file:bg-green-50 file:text-green-700 hover:file:bg-green-100" 
                           onchange="previewImage(event)">
                </div>
                
                <div class="grid grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div>
                        <label for="full_name" class="block text-sm font-medium">Full Name</label>
                        <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                      bg-white text-gray-800 px-3 py-2">
                    </div>
                    
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium">Username</label>
                        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                      bg-white text-gray-800 px-3 py-2">
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium">Email</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                      bg-white text-gray-800 px-3 py-2">
                    </div>
                    
                    <!-- Phone Number -->
                    <div>
                        <label for="phone_number" class="block text-sm font-medium">Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                      bg-white text-gray-800 px-3 py-2">
                    </div>
                    
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" id="password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                      bg-white text-gray-800 px-3 py-2">
                    </div>
                    
                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 
                                      bg-white text-gray-800 px-3 py-2">
                    </div>
                </div>
                
                <div class="flex justify-center mt-6">
                    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                        Save Changes
                    </button>
                </div>
            </form>
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