<?php

$servername = "localhost";
$username = "root";
$password = "Sham13Ram"; // MySQL root password 
$dbname = "splitwise_clone";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
