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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Note Taking and Whiteboard</title>
    <style>
        textarea {
            width: 100%;
            height: 100px; /* Adjust height as needed */
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            outline: none;
            resize: vertical; /* Allow vertical resizing */
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            color: #333;
            transition: background-color 0.5s, color 0.5s;
        }
        
        .dark-mode body {
            background-color: #333;
            color: #eee;
        }
        
        #container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            padding: 20px;
            height: calc(100vh - 40px);
            position: relative;
        }
        
        .card {
            background-color: rgba(255, 255, 255, 0.6);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            width: 50vw;
            height: 80vh;
            overflow: auto;
            transition: transform 0.5s, box-shadow 0.5s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }


        .card.dark-mode {
            background-color: rgba(0, 0, 0, 0.6);
            box-shadow: 0 4px 16px rgba(255, 255, 255, 0.1);
        }
        
        h2 {
            margin: 0 0 10px;
            font-size: 24px;
        }
        
        label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        input[type="color"],
        input[type="range"],
        input[type="number"] {
            appearance: none;
            width: 100%;
            height: 20px;
            border-radius: 5px;
            outline: none;
        }
        
        input[type="color"]::-webkit-color-swatch-wrapper {
            padding: 0;
        }
        
        input[type="color"]::-webkit-color-swatch {
            border: none;
            border-radius: 5px;
        }
        
        input[type="range"]::-webkit-slider-thumb,
        input[type="number"]::-webkit-inner-spin-button {
            appearance: none;
            width: 20px;
            height: 20px;
            background-color: #4CAF50;
            border-radius: 50%;
            cursor: pointer;
        }
        
        input[type="range"]::-moz-range-thumb {
            width: 20px;
            height: 20px;
            background-color: #4CAF50;
            border-radius: 50%;
            cursor: pointer;
        }
        
        canvas {
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            cursor: crosshair;
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            max-width: 100%;
            max-height: 100%;
        }

        
        button {
            padding: 10px 20px;
            border: none;
            background-color: #4CAF50;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        button:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }
        
        .tool-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tool-options label {
            font-size: 14px;
        }
        
        .tool-options select {
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
        }
        
/* Your existing CSS styles */

/* Your existing CSS styles */

.dark-mode-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 40px;
    height: 20px;
    border-radius: 10px;
    background-color: #ccc;
    cursor: pointer;
    overflow: hidden;
    display: flex;
    align-items: center;
}

.toggle-track {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 10px;
    background-color: #333;
    transition: background-color 0.3s;
}

.toggle-thumb {
    position: absolute;
    top: 0;
    left: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #fff;
    box-shadow: 0 0 2px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s;
}

.dark-mode .dark-mode-toggle .toggle-track {
    background-color: #999;
}

.dark-mode .dark-mode-toggle .toggle-thumb {
    transform: translateX(20px);
}
        
        .dark-mode body {
            background-color: #333;
            color: #eee;
        }
        
        /* Add styles for other elements in dark mode */
        .dark-mode #container {
            background-color: #222;
        }
        
        .dark-mode .card {
            background-color: rgba(255, 255, 255, 0.2); /* Adjust opacity for better readability */
            box-shadow: 0 4px 16px rgba(255, 255, 255, 0.1);
        }
        
        .dark-mode h2 {
            color: #eee;
        }
        
        .dark-mode label {
            color: #ddd;
        }
        
        .dark-mode input[type="color"],
        .dark-mode input[type="range"],
        .dark-mode input[type="number"] {
            background-color: #444;
            color: #eee;
            border: 1px solid #555;
        }
        
        .dark-mode button {
            background-color: #4CAF50;
            color: #fff;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        .dark-mode button:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }
        
        .dark-mode .tool-options select {
            background-color: #444;
            color: #eee;
            border: 1px solid #555;
        }
        
        .dark-mode .options-toggle {
            background-color: rgba(255, 255, 255, 0.2); /* Adjust opacity for better readability */
        }
        
        .options-toggle {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            z-index: 1;
            transition: transform 0.3s;
            background-image: url('path/to/favicon.ico');
            background-size: cover;
            background-repeat: no-repeat;
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        
        .options-toggle:hover {
            transform: rotate(90deg);
            background-color: rgba(0, 0, 0, 0.6);
        }
        
        
    </style>
</head>
<body>
    <div id="container">
        <div class="card" id="notes">
            <div class="options-toggle" onclick="toggleView('whiteboard')">
                &#9776; <!-- Unicode symbol for the menu icon -->
            </div>
            
            <h2>Notes <span>&#128221;</span></h2>
            
            <div class="tool-options">
            <label for="font">Font:</label>
<select id="font" onchange="applyTextStyle()">
    <option value="Arial">Arial</option>
    <option value="Verdana">Verdana</option>
    <option value="Georgia">Georgia</option>
</select>

<label for="fontSize">Font Size:</label>
<select id="fontSize" onchange="applyTextStyle()">
    <option value="12px">12</option>
    <option value="14px">14</option>
    <option value="16px">16</option>
</select>

<label for="fontColor">Font Color:</label>
<input type="color" id="fontColor" onchange="applyTextStyle()" value="#000000">


                
            </div>
            
            <textarea id="noteInput" cols="30" rows="10"></textarea>
            <button onclick="saveNote()">Save Note</button>
            <ul id="noteList"></ul>
            <!-- Add an empty list to display saved notes -->
            <ul id="savedNotes"></ul>

            <button onclick="saveNotesToFile()">Save Notes to File</button>
        </div>
        <div class="card" id="whiteboard" style="display: none;">
            <div class="options-toggle" onclick="toggleView('notes')">
                &#9776; </div>
            <h2>Whiteboard <span>&#9997;</span></h2>
            <div class="tool-options">
                <label for="penColor">Pen Color:</label>
                <input type="color" id="penColor" value="#000000">
                <label for="penSize">Pen Size:</label>
                <input type="range" id="penSize" min="1" max="20" value="5">
                <label for="eraser">Eraser:</label>
                <input type="checkbox" id="eraser" onchange="toggleEraser()">
                    <label for="canvasWidth">Canvas Width:</label>
                    <input type="number" id="canvasWidth" value="500">
                    <label for="canvasHeight">Canvas Height:</label>
                    <input type="number" id="canvasHeight" value="300">
                    <button onclick="resizeCanvas()">Apply</button>
                
            </div>
            <canvas id="canvas" width="700" height="500"></canvas>
            <button onclick="saveWhiteboard()">Save Whiteboard</button>
        </div>
        <div class="background-overlay"></div>
    </div>
    <div class="dark-mode-toggle" onclick="toggleDarkMode()">
        <div class="toggle-track"></div>
        <div class="toggle-thumb"></div>
    </div>
    
        

    <script>
        // Function to toggle between notes and whiteboard
        function toggleView(view) {
            var notes = document.getElementById('notes');
            var whiteboard = document.getElementById('whiteboard');
            var cardToShow = view === 'notes' ? notes : whiteboard;
            var cardToHide = view === 'notes' ? whiteboard : notes;

            cardToHide.style.transform = 'scale(0.8)';
            setTimeout(() => {
                cardToHide.style.display = 'none';
                cardToShow.style.display = 'flex';
                setTimeout(() => {
                    cardToShow.style.transform = 'scale(1)';
                }, 50);
            }, 500);
        }

        // Toggle dark mode function
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
        }
        

        // Function to save note
        function saveNote() {
            var noteInput = document.getElementById('noteInput').value;
            var noteList = document.getElementById('noteList');
            var li = document.createElement('li');
            li.textContent = noteInput;
            noteList.appendChild(li);
            document.getElementById('noteInput').value = '';
        }

        // Function to save notes to file
        function saveNotesToFile() {
            var notes = document.getElementById('noteList').innerText;
            var blob = new Blob([notes], { type: "text/plain;charset=utf-8" });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = 'notes.txt';
            link.click();
        }

        // Function to save whiteboard
        function saveWhiteboard() {
            var canvas = document.getElementById('canvas');
            var dataURL = canvas.toDataURL("image/png");
            var link = document.createElement('a');
            link.href = dataURL;
            link.download = 'whiteboard.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

// Whiteboard functionality
var canvas = document.getElementById('canvas');
var ctx = canvas.getContext('2d');
var painting = false;
var eraser = false;
function resizeCanvas() {
    var canvas = document.getElementById('canvas');
    var width = document.getElementById('canvasWidth').value;
    var height = document.getElementById('canvasHeight').value;
    canvas.width = width;
    canvas.height = height;
    clearCanvas();
}

function clearCanvas() {
    var canvas = document.getElementById('canvas');
    var ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

canvas.addEventListener('mousedown', startPosition);
canvas.addEventListener('mouseup', endPosition);
canvas.addEventListener('mousemove', draw);

function startPosition(e) {
    painting = true;
    draw(e);
}

function endPosition() {
    painting = false;
    ctx.beginPath();
}

function draw(e) {
    if (!painting) return;
    
    var rect = canvas.getBoundingClientRect();
    var scaleX = canvas.width / rect.width;   // Scale factor for X coordinate
    var scaleY = canvas.height / rect.height; // Scale factor for Y coordinate
    var x = (e.clientX - rect.left) * scaleX;
    var y = (e.clientY - rect.top) * scaleY;

    ctx.lineWidth = document.getElementById('penSize').value;
    ctx.lineCap = 'round';
    if (eraser) {
        ctx.strokeStyle = '#ffffff'; // Use white color for eraser
    } else {
        ctx.strokeStyle = document.getElementById('penColor').value;
    }

    ctx.lineTo(x, y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(x, y);
}


// Toggle eraser function
function toggleEraser() {
    eraser = document.getElementById('eraser').checked;
}

// Function to handle keyboard shortcuts
document.addEventListener('keydown', function(event) {

    document.getElementById('noteInput').focus();
    // Check if Ctrl and Shift keys are pressed
    const ctrlKey = event.ctrlKey || event.metaKey;
    const shiftKey = event.shiftKey;

    // Prevent default behavior if the event's key is not the 'Control' key
    if (event.key.includes('Control')) {
        event.preventDefault();
    }

    // Check for shortcut key combinations
    if (ctrlKey && shiftKey) {
        switch (event.key) {
            case '=':
            case '+':
                increasePenSize();
                break;
            case '-':
                decreasePenSize();
                break;
            case 'e':
                toggleEraser();
                break;
        }
    }
});

// Function to increase pen size
function increasePenSize() {
    var penSizeInput = document.getElementById('penSize');
    var newValue = parseInt(penSizeInput.value) + 1;
    if (newValue <= parseInt(penSizeInput.max)) {
        penSizeInput.value = newValue;
        draw();
    }
}
// Function to toggle eraser
// Function to toggle eraser
function toggleEraser() {
    eraser = !eraser;
    var eraserCheckbox = document.getElementById('eraser');
    eraserCheckbox.checked = eraser; // Check or uncheck the checkbox based on the eraser state
}

// Function to decrease pen size
function decreasePenSize() {
    var penSizeInput = document.getElementById('penSize');
    var newValue = parseInt(penSizeInput.value) - 1;
    if (newValue >= parseInt(penSizeInput.min)) {
        penSizeInput.value = newValue;
        draw();
    }
}
// Front-end JavaScript code

// Function to save note
function saveNote() {
    var noteInput = document.getElementById('noteInput').value;
    var font = document.getElementById('font').value;
    var fontSize = document.getElementById('fontSize').value;
    var fontColor = document.getElementById('fontColor').value;

    // Apply styles to the note content
    var styledNote = '<span style="font-family: ' + font + '; font-size: ' + fontSize + '; color: ' + fontColor + ';">' + noteInput + '</span>';

    // Save the styled note to the database
    fetch('save_note.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'note=' + encodeURIComponent(styledNote),
    })
    .then(response => {
        // Reload the page after saving the note
        location.reload();
    })
    .catch(error => {
        // Handle error
    });
}


// Function to load note history
function loadNoteHistory() {
    fetch('fetch_notes.php')
    .then(response => response.json())
    .then(data => {
        // Display the note history in the notes section
        var noteList = document.getElementById('noteList');
        noteList.innerHTML = '';
        data.forEach(note => {
            var li = document.createElement('li');
            li.textContent = note;
            noteList.appendChild(li);
        });
    })
    .catch(error => {
        // Handle error
    });
}
// Function to load saved notes history
function loadSavedNotes() {
    fetch('fetch_notes.php')
    .then(response => response.json())
    .then(data => {
        // Display the saved notes history
        var savedNotesList = document.getElementById('savedNotes');
        savedNotesList.innerHTML = '';
        data.forEach(note => {
            var li = document.createElement('li');
            li.textContent = note;
            
            // Add edit button
            var editButton = document.createElement('button');
            editButton.textContent = 'Edit';
            editButton.addEventListener('click', function() {
                // Allow user to edit the note
                var updatedNote = prompt('Edit your note:', note);
                if (updatedNote !== null) {
                    updateNoteInDatabase(note, updatedNote);
                }
            });
            
            // Add delete button
            var deleteButton = document.createElement('button');
            deleteButton.textContent = 'Delete';
            deleteButton.addEventListener('click', function() {
                // Delete the note from the database
                deleteNoteFromDatabase(note);
            });
            
            // Append buttons to note
            li.appendChild(editButton);
            li.appendChild(deleteButton);
            
            // Append note to list
            savedNotesList.appendChild(li);
        });
    })
    .catch(error => {
        // Handle error
    });
}

// Function to update note in database
function updateNoteInDatabase(oldNote, newNote) {
    fetch('update_note.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'old_note=' + encodeURIComponent(oldNote) + '&new_note=' + encodeURIComponent(newNote),
    })
    .then(response => {
        // Reload saved notes after updating
        loadSavedNotes();
    })
    .catch(error => {
        // Handle error
    });
}
function applyTextStyle() {
    var noteInput = document.getElementById('noteInput');
    var font = document.getElementById('font').value;
    var fontSize = document.getElementById('fontSize').value;
    var fontColor = document.getElementById('fontColor').value;

    // Apply styles to the note input element
    noteInput.style.fontFamily = font;
    noteInput.style.fontSize = fontSize;
    noteInput.style.color = fontColor;
}

// Function to delete note from database
function deleteNoteFromDatabase(note) {
    fetch('delete_note.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'note=' + encodeURIComponent(note),
    })
    .then(response => {
        // Reload saved notes after deleting
        loadSavedNotes();
    })
    .catch(error => {
        // Handle error
    });
}

// Load saved notes on page load
loadSavedNotes();

    </script>
</body>
</html>