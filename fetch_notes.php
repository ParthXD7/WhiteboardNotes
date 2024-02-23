<?php

// Connect to MySQL database
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$database = "notes_db"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch note history from database
$sql = "SELECT * FROM notes ORDER BY timestamp DESC";
$result = $conn->query($sql);

$notes = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row["content"];
    }
}

// Close database connection
$conn->close();

// Output note history as JSON
echo json_encode($notes);

?>
