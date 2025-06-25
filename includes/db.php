<?php
$host = "localhost";      // Usually localhost for local dev
$username = "root";       // Default XAMPP username
$password = "";           // Default XAMPP password is empty
$dbname = "bank_system";  // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
