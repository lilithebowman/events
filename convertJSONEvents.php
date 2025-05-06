<?php //convertJSONEvents.php
// filepath: /home/lilithe/public_html/events/convertJSONEvents.php

// Set the content type to JSON for API-like responses
header('Content-Type: application/json');

// Database configuration (same as in saveEvent.php)
$dbHost = 'localhost';
$dbUser = getenv('MYSQL_USERNAME'); // Same as in saveEvent.php
$dbPass = getenv('MYSQL_PASSWORD'); // Use environment variable for security
$dbName = 'events';

// Path to the JSON file
$jsonFile = 'events.json';

// Check if the JSON file exists
if (!file_exists($jsonFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Events JSON file not found.']);
    exit;
}

// Read the JSON file
$jsonContent = file_get_contents($jsonFile);
$events = json_decode($jsonContent, true);

// Check if JSON decoding was successful
if ($events === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to decode JSON file: ' . json_last_error_msg()]);
    exit;
}

// Connect to MySQL
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

// Prepare statistics
$stats = [
    'total' => count($events),
    'success' => 0,
    'failed' => 0,
    'errors' => []
];

// Clear existing events first to prevent duplicates
$clearSql = "TRUNCATE TABLE events";
if (!$conn->query($clearSql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to clear existing events: ' . $conn->error]);
    exit;
}

// Loop through each event and insert it into the database
foreach ($events as $event) {
    // Prepare fields with proper escaping
    $name = $conn->real_escape_string($event['name'] ?? '');
    $date = $conn->real_escape_string($event['date'] ?? '');
    $startTime = $conn->real_escape_string($event['startTime'] ?? '');
    $endTime = $conn->real_escape_string($event['endTime'] ?? '');
    $location = $conn->real_escape_string($event['location'] ?? '');
    $host = $conn->real_escape_string($event['host'] ?? '');
    $description = $conn->real_escape_string($event['description'] ?? '');
    $googleMapsLink = $conn->real_escape_string($event['googleMapsLink'] ?? '');
    $image = $conn->real_escape_string($event['image'] ?? '');
    
    // Convert guestList to JSON for storage
    $guestList = '';
    if (isset($event['guestList']) && is_array($event['guestList'])) {
        $guestList = $conn->real_escape_string(json_encode($event['guestList']));
    }
    
    // Standardize date format if needed
    if (!empty($date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        // Try to parse the date string into a standard format
        $parsedDate = date_create($date);
        if ($parsedDate) {
            $date = date_format($parsedDate, 'Y-m-d');
        }
    }

    // Insert the event into the database
    $sql = "INSERT INTO events (name, date, startTime, endTime, location, host, description, googleMapsLink, guestList, image)
            VALUES ('$name', '$date', '$startTime', '$endTime', '$location', '$host', '$description', '$googleMapsLink', '$guestList', '$image')";

    if ($conn->query($sql)) {
        $stats['success']++;
    } else {
        $stats['failed']++;
        $stats['errors'][] = [
            'event' => $name,
            'error' => $conn->error
        ];
    }
}

// Close the database connection
$conn->close();

// Return results
http_response_code(200);
echo json_encode([
    'status' => 'complete',
    'message' => "Conversion completed: {$stats['success']} events imported successfully, {$stats['failed']} failed.",
    'statistics' => $stats
]);
