<?php
// Start the session if it's not already started
session_start();

// Include GitHub upload function
require_once 'github_upload.php'; // Save the previous function in this file

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
    $sql = "SELECT full_name, email, username, phone_number, profile_pic FROM client_acc WHERE username = ?";
} else {
    $sql = "SELECT full_name, email, username, phone_number, profile_pic FROM client_acc WHERE email = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login_identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $full_name = $user_data['full_name'];
    $email = $user_data['email'];
    $username = $user_data['username'];
    $phone_number = $user_data['phone_number'];
    $profile_pic = $user_data['profile_pic'];
    // Setting a default user type
    $user_type = "Client"; 
} else {
    // Handle case where user data is not found
    $full_name = "User";
    $email = $login_identifier;
    $username = "";
    $phone_number = "";
    $profile_pic = null;
    $user_type = "User";
}

$stmt->close();

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
    // Collect form data
    $new_full_name = $_POST['full_name'];
    $new_email = $_POST['email'];
    $new_username = $_POST['username'];
    $new_phone_number = $_POST['phone_number'];
    $new_password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : null;
    
    // Validate password if provided
    if ($new_password !== null) {
        if ($new_password !== $confirm_password) {
            $update_error = "Passwords do not match!";
        } else {
            // Hash the password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }
    
    // Proceed with update if no errors
    if (empty($update_error)) {
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
        
        // Add password if it was provided
        if ($new_password !== null) {
            $sql_parts[] = "password = ?";
            $param_types .= "s";
            $param_values[] = $hashed_password;
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
                            // Try to upload to GitHub
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
            $sql = "UPDATE client_acc SET " . implode(", ", $sql_parts) . " WHERE ";
            
            // Determine which field to use for the WHERE clause
            if (isset($_SESSION['username'])) {
                $sql .= "username = ?";
            } else {
                $sql .= "email = ?";
            }
            
            $param_types .= "s";
            $param_values[] = $login_identifier;
            
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
                    
                    // Update session variables if username or email changed
                    if ($login_identifier == $_SESSION['username'] && $new_username != $login_identifier) {
                        $_SESSION['username'] = $new_username;
                    } else if ($login_identifier == $_SESSION['email'] && $new_email != $login_identifier) {
                        $_SESSION['email'] = $new_email;
                    }
                    
                    // Redirect to refresh the page with new data
                    header("Location: clientprofile.php?message=" . urlencode($update_message));
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
    <!-- Rest of HTML head remains the same -->
    <!-- ... -->
</head>
<body class="flex">
    <!-- Rest of HTML body remains the same -->
    <!-- ... -->
</body>
</html>