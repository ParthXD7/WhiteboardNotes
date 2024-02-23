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

// Update note in database
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["old_note"]) && isset($_POST["new_note"])) {
    $oldNote = $_POST["old_note"];
    $newNote = $_POST["new_note"];

    $sql = "UPDATE notes SET content = ? WHERE content = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $newNote, $oldNote);
    $stmt->execute();
    $stmt->close();
}

// Close database connection
$conn->close();

?>
