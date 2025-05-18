<?php
// $servername = "localhost";  
// $username = "root";         
// $password = "";             
// $database = "capstone"; 
$conn = new mysqli("i4g8gso0goc8cws8cocc4ks0", "root", "uz9Fb2ZvJVlwLYYoGwTieloCHFY0Yv3uqN9XUDkDJlHy8QZL4x6jdZQDOvW3cZDV", "capstone"); //prod connection


// $conn = new mysqli($servername, $username, $password, $database);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
