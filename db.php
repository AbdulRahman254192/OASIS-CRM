<?php
// Notice the double backslash below! PHP requires \\ to read a single \
$serverName = "DESKTOP-L3L25KV\\SQLEXPRESS"; 

$connectionOptions = array(
    "Database" => "OasisHMS",
    "Uid" => "oasisuser",
    "PWD" => "12345",
    "TrustServerCertificate" => true // Added this based on your screenshot!
);

// Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

if($conn) {
   // echo "<h1>✅ Connected Successfully!</h1>";
} else {
    echo "<h1>❌ Connection Failed</h1>";
    die(print_r(sqlsrv_errors(), true));
}
