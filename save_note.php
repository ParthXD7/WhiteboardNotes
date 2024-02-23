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

// Save note to database
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["note"])) {
    $note = $_POST["note"];

    $sql = "INSERT INTO notes (content) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $note);
    $stmt->execute();
    $stmt->close();
}

// Close database connection
$conn->close();

?>
