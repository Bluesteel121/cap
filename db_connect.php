<?php
// Create a shared data initialization file: db_connect.php

// 1. Create this file and include it at the top of both farmerprofile.php and farmerpage.php
// Save this as db_connect.php in the same directory as your other files

session_start();

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Centralized function to get farmer data
function getFarmerData($conn) {
    $farmer_id = null;
    $login_field = null;
    $login_identifier = null;
    
    // First check if farmer_id exists in session
    if (isset($_SESSION['farmer_id']) && !empty($_SESSION['farmer_id'])) {
        $farmer_id = $_SESSION['farmer_id'];
        $sql = "SELECT * FROM farmer_acc WHERE farmer_id = ?";
        $stmt = $conn->prepare("SELECT SQL_NO_CACHE * FROM farmer_acc WHERE farmer_id = ? LIMIT 1");
        $stmt->bind_param("i", $farmer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    // Then check various login methods
    elseif (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $login_field = "username";
        $login_identifier = $_SESSION['username'];
        $sql = "SELECT * FROM farmer_acc WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $login_identifier);
    }
    elseif (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
        $login_field = "email";
        $login_identifier = $_SESSION['email'];
        $sql = "SELECT * FROM farmer_acc WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $login_identifier);
    }
    elseif (isset($_SESSION['contact_num']) && !empty($_SESSION['contact_num'])) {
        $login_field = "contact_num";
        $login_identifier = $_SESSION['contact_num'];
        $sql = "SELECT * FROM farmer_acc WHERE contact_num = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $login_identifier);
    }
    else {
        // Fallback - query first farmer
        $sql = "SELECT * FROM farmer_acc LIMIT 1";
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $farmer_data = $row;
        // Always save farmer_id to session for future use
        $_SESSION['farmer_id'] = $row['farmer_id'];
    } else {
        // Default data
        $farmer_data = [
            'farmer_id' => 0,
            'name' => "Unknown Farmer",
            'username' => "Farmer",
            'email' => "",
            'contact_num' => "",
            'profile_picture' => null,
            'status' => "Active",
            'flowered' => 0,
            'pested' => 0,
            'total_planted' => 0,
            'plantation_area' => "0.0",
            'last_harvest' => "0 pcs",
            'fertilizer_data' => '[]',
            'pending_orders' => 0
        ];
    }
    
    $stmt->close();
    return $farmer_data;
}

// Function to display profile image
function displayProfileImage($profile_pic) {
    if ($profile_pic && strlen($profile_pic) > 0) {
        return $profile_pic;
    } else {
        return "profile.jpg";
    }
}
?>