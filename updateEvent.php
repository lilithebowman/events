<?php
// filepath: /home/lilithe/public_html/events/updateEvent.php

// Set the content type to JSON
header('Content-Type: application/json');

// Path to the JSON file
$jsonFile = 'events.json';
$backupFile = 'oldEvents.json'; // Backup file path

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

// Get the input data
$input = json_decode(file_get_contents('php://input'), true);

// Validate the input data
if (!isset($input['updatedEvent']) || !isset($input['updatedEvent']['name']) || !isset($input['updatedEvent']['date'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid input data.']);
    exit;
}

$updatedEvent = $input['updatedEvent'];

// Load the existing events from the JSON file
if (!file_exists($jsonFile)) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Events file not found.']);
    exit;
}

$events = json_decode(file_get_contents($jsonFile), true);
if (!is_array($events)) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to decode events file.']);
    exit;
}

// Backup the current events.json as oldEvents.json
if (!copy($jsonFile, $backupFile)) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to create a backup of the events file.']);
    exit;
}

// Find the event with the same name and date
$eventFound = false;
foreach ($events as $event) { // Use reference to modify the event in place
    if ($event['name'] === $updatedEvent['name'] && $event['date'] === $updatedEvent['date']) {
        // Update the event details
        $event = $updatedEvent;
        $eventFound = true;
        break;
    }
}

// If the event was not found, return an error
if (!$eventFound) {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'Event not found.']);
    exit;
}

// Save the updated events back to the JSON file
if (file_put_contents($jsonFile, json_encode($events, JSON_PRETTY_PRINT))) {
    http_response_code(200); // OK
    echo json_encode(['success' => 'Event updated successfully.']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to save updated events.']);
}
?>
