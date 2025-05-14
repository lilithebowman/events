<?php
// filepath: /home/lilithe/public_html/events/saveEvent.php

// Database configuration
$dbHost = 'localhost';
$dbUser = getenv('MYSQL_USERNAME');
$dbPass = getenv('MYSQL_PASSWORD'); // Use environment variables for security
$dbName = 'events';

// Directory to store uploaded images
$imageUploadDir = 'uploads/';

// Ensure the upload directory exists
if (!is_dir($imageUploadDir)) {
    mkdir($imageUploadDir, 0777, true);
}

// Create database connection
$conn = new mysqli($dbHost, $dbUser, $dbPass);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Create the database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbName";
if (!$conn->query($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create database: ' . $conn->error]);
    exit;
}

// Select the database
$conn->select_db($dbName);

// Create events table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS events (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    startTime VARCHAR(50) NOT NULL,
    endTime VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    host VARCHAR(255),
    description TEXT NOT NULL,
    googleMapsLink VARCHAR(512),
    guestList TEXT,
    image VARCHAR(255)
)";

if (!$conn->query($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create table: ' . $conn->error]);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the form was submitted with a file
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Image upload failed or no image provided.']);
        exit;
    }

    // Validate and move the uploaded image
    $imageFile = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($imageFile['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid image type. Only JPEG, PNG, and GIF are allowed.']);
        exit;
    }

    // Generate a unique filename for the image
    $imageFilename = uniqid('event_', true) . '.' . pathinfo($imageFile['name'], PATHINFO_EXTENSION);
    $imagePath = $imageUploadDir . $imageFilename;

    // Move the uploaded file to the upload directory
    if (!move_uploaded_file($imageFile['tmp_name'], $imagePath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save the uploaded image.']);
        exit;
    }

    // Collect other form data
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $date = $conn->real_escape_string($_POST['date'] ?? '');
    $startTime = $conn->real_escape_string($_POST['startTime'] ?? '');
    $endTime = $conn->real_escape_string($_POST['endTime'] ?? '');
    $location = $conn->real_escape_string($_POST['location'] ?? '');
    $host = $conn->real_escape_string($_POST['host'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $googleMapsLink = $conn->real_escape_string($_POST['googleMapsLink'] ?? '');
    
    // Convert guestList to JSON for storage
    $guestList = '';
    if (isset($_POST['guestList'])) {
        $guests = array_map('trim', explode(',', $_POST['guestList']));
        $guestObjects = array_map(function($name) {
            return ['name' => $name];
        }, $guests);
        $guestList = $conn->real_escape_string(json_encode($guestObjects));
    }

    // Validate the event data
    if (empty($name) || empty($date) || empty($startTime) || empty($endTime) || empty($location) || empty($description)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid event data.']);
        exit;
    }

    // Insert the event into the database
    $sql = "INSERT INTO events (name, date, startTime, endTime, location, host, description, googleMapsLink, guestList, image)
            VALUES ('$name', '$date', '$startTime', '$endTime', '$location', '$host', '$description', '$googleMapsLink', '$guestList', '$imagePath')";

    if ($conn->query($sql)) {
        // After successfully saving the event, generate the events.json file
        generateEventsJSON($conn);
        
        http_response_code(200);
        echo json_encode(['success' => 'Event saved successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save the event: ' . $conn->error]);
    }

    // Close the database connection
    $conn->close();
} else {
    // If the request method is not POST, return an error
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']);
}

/**
 * Function to generate events.json file from the MySQL database
 * 
 * @param mysqli $conn Database connection
 */
function generateEventsJSON($conn) {
    // Define the path to events.json
    $jsonFilePath = 'events.json';
    
    // Backup the old file if it exists
    if (file_exists($jsonFilePath)) {
        copy($jsonFilePath, 'oldEvents.json');
    }
    
    // Get current date to filter out past events
    $currentDate = date('Y-m-d');
    
    // Query to get all upcoming events, ordered by date
    $sql = "SELECT * FROM events WHERE date >= '$currentDate' ORDER BY date ASC";
    $result = $conn->query($sql);
    
    $events = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Process the guestList from JSON back to an array
            $guestList = [];
            if (!empty($row['guestList'])) {
                $guestList = json_decode($row['guestList'], true) ?: [];
            }
            
            // Build the event object
            $event = [
                'id' => $row['id'],
                'name' => $row['name'],
                'date' => $row['date'],
                'startTime' => $row['startTime'],
                'endTime' => $row['endTime'],
                'location' => $row['location'],
                'host' => $row['host'],
                'description' => $row['description'],
                'googleMapsLink' => $row['googleMapsLink'],
                'guestList' => $guestList,
                'image' => $row['image']
            ];
            
            $events[] = $event;
        }
    }
    
    // Write the events to the JSON file
    file_put_contents($jsonFilePath, json_encode($events, JSON_PRETTY_PRINT));
}