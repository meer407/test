<?php

// Establishing a MySQL database connection
$conn = new mysqli('localhost', 'root', '', 'online');

// Checking if the connection was successful
if ($conn->connect_error) {
    // If connection fails, output an error message and terminate script execution
    die("Could not connect to MySQL: " . $conn->connect_error);
}

// If the connection is successful, you can proceed with your database operations

?>
