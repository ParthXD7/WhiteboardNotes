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

// Delete note from database
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["note"])) {
    $note = $_POST["note"];

    $sql = "DELETE FROM notes WHERE content = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $note);
    $stmt->execute();
    $stmt->close();
}

// Close database connection
$conn->close();

?>
