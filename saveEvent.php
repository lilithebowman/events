<?php
// filepath: /home/lilithe/public_html/events/saveEvent.php

// Path to the JSON file database
$jsonFile = 'events.json';
// Directory to store uploaded images
$imageUploadDir = 'uploads/';

// Ensure the upload directory exists
if (!is_dir($imageUploadDir)) {
    mkdir($imageUploadDir, 0777, true);
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
    $eventData = [
        'name' => $_POST['name'] ?? '',
        'date' => $_POST['date'] ?? '',
        'startTime' => $_POST['startTime'] ?? '',
        'endTime' => $_POST['endTime'] ?? '',
        'location' => $_POST['location'] ?? '',
        'host' => $_POST['host'] ?? '',
        'description' => $_POST['description'] ?? '',
        'googleMapsLink' => $_POST['googleMapsLink'] ?? '',
        'guestList' => isset($_POST['guestList']) ? array_map('trim', explode(',', $_POST['guestList'])) : [],
        'image' => $imagePath
    ];

    // Validate the event data
    if (empty($eventData['name']) || empty($eventData['date']) || empty($eventData['startTime']) || empty($eventData['endTime']) || empty($eventData['location']) || empty($eventData['description'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid event data.']);
        exit;
    }

    // Load existing events from the JSON file
    $events = [];
    if (file_exists($jsonFile)) {
        $events = json_decode(file_get_contents($jsonFile), true);
        if (!is_array($events)) {
            $events = [];
        }
    }

    // Add the new event to the events array
    $events[] = $eventData;

    // Save the updated events array back to the JSON file
    if (file_put_contents($jsonFile, json_encode($events, JSON_PRETTY_PRINT))) {
        http_response_code(200);
        echo json_encode(['success' => 'Event saved successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save the event.']);
    }
} else {
    // If the request method is not POST, return an error
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']);
}
