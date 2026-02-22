<?php
$servername = "localhost";
$username = "root";
$password = "Sham13Ram"; // replace with your MySQL root password if you have set one
$dbname = "splitwise_clone";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>
