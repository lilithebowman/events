<?php
// filepath: /home/lilithe/public_html/events/saveEvent.php

// Path to the JSON file database
$jsonFile = 'events.json';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Get the raw POST data
	$postData = file_get_contents('php://input');
	$eventData = json_decode($postData, true);

	// Validate the event data
	if (!isset($eventData['name'], $eventData['date'], $eventData['startTime'], $eventData['endTime'], $eventData['location'], $eventData['description'])) {
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
?>