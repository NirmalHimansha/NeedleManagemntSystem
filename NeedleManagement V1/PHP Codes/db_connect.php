<?php
// =================================================================
// FILE: db_connect.php
// This file connects to the database. It will be included in other files.
// =================================================================

// --- Database Credentials ---
// These are the default credentials for a local XAMPP server.
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'FindPW@165';
$db_name = 'needle_management_system';

// --- Create Connection ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- Check Connection ---
// If the connection fails, stop the script and show an error.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to utf8mb4 for full Unicode support.
$conn->set_charset("utf8mb4");

?>
