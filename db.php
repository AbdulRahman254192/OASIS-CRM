<?php
// Default MySQL credentials for local development (like XAMPP)
$servername = "localhost"; 
$username = "root"; // Default MySQL username
$password = "CALLOFDUTY"; // Default MySQL password is usually empty, change if you set one in Workbench
$dbname = "hotel"; // The name of your database in Workbench

// Establish the MySQL connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check if the connection was successful
if (!$conn) {
    die("❌ MySQL Connection Failed: " . mysqli_connect_error());
}
// echo "✅ Connected to MySQL Successfully!";
?>