<?php
// filepath: /home/lilithe/public_html/events/editEvent.php

// Database configuration
$dbHost = 'localhost';
$dbUser = getenv('MYSQL_USERNAME'); // Use environment variables for security
$dbPass = getenv('MYSQL_PASSWORD'); // Use environment variables for security
$dbName = 'events';

// Directory to store uploaded images
$imageUploadDir = 'uploads/';

// Ensure the upload directory exists
if (!is_dir($imageUploadDir)) {
	mkdir($imageUploadDir, 0777, true);
}

// Set the content type to JSON
header('Content-Type: application/json');

// Create database connection
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
	http_response_code(500);
	echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
	exit;
}

// Handle GET request to load events
if (!isset($_POST['id'])) {
	// If ID is provided, load a specific event
	if (isset($_GET['id'])) {
		$eventId = intval($_GET['id']);
		
		$sql = "SELECT * FROM events WHERE id = $eventId";
		$result = $conn->query($sql);
		
		if ($result && $result->num_rows > 0) {
			$event = $result->fetch_assoc();
			
			// Process guestList JSON
			if (!empty($event['guestList'])) {
				$event['guestList'] = json_decode($event['guestList'], true) ?: [];
			} else {
				$event['guestList'] = [];
			}
			
			echo json_encode(['success' => true, 'event' => $event]);
		} else {
			http_response_code(404);
			echo json_encode(['error' => 'Event not found']);
		}
	} else {
		// Get current date to filter out past events
		$currentDate = date('Y-m-d');
	
		// Query to get all upcoming events, ordered by date
		$sql = "SELECT * FROM events WHERE date >= '$currentDate' ORDER BY date ASC";
		$result = $conn->query($sql);
		
		$events = [];
		if ($result && $result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$events[] = $row;
			}
		}
		
		echo json_encode(['success' => true, 'events' => $events]);
	}
}
// Handle POST request to update an event
else if (isset($_POST['id'])) {
	// Check if the event ID is provided
	if (!isset($_POST['id']) || empty($_POST['id'])) {
		http_response_code(400);
		echo json_encode(['error' => 'Event ID is required']);
		exit;
	}
	
	$eventId = intval($_POST['id']);
	
	// Get existing event to check if it exists
	$checkSql = "SELECT * FROM events WHERE id = $eventId";
	$checkResult = $conn->query($checkSql);
	
	if (!$checkResult || $checkResult->num_rows === 0) {
		http_response_code(404);
		echo json_encode(['error' => 'Event not found']);
		exit;
	}
	
	// Process the uploaded image if provided
	$imagePath = null;
	if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
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
	} else if (isset($_POST['imageUrl']) && !empty($_POST['imageUrl'])) {
		// Use the existing image URL
		$imagePath = $_POST['imageUrl'];
	}
	
	// Collect and sanitize form data
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
	if (isset($_POST['guestList']) && !empty($_POST['guestList'])) {
		$guests = array_map('trim', explode(',', $_POST['guestList']));
		$guestObjects = array_map(function($name) {
			return ['name' => $name];
		}, $guests);
		$guestList = $conn->real_escape_string(json_encode($guestObjects));
	}
	
	// Validate the event data
	if (empty($name) || empty($date) || empty($startTime) || empty($endTime) || empty($location) || empty($description)) {
		http_response_code(400);
		echo json_encode(['error' => 'Required fields cannot be empty.']);
		exit;
	}
	
	// Build the SQL query based on whether an image was uploaded
	if ($imagePath) {
		$sql = "UPDATE events SET 
				name = '$name', 
				date = '$date', 
				startTime = '$startTime', 
				endTime = '$endTime', 
				location = '$location', 
				host = '$host', 
				description = '$description', 
				googleMapsLink = '$googleMapsLink', 
				guestList = '$guestList', 
				image = '$imagePath'
				WHERE id = $eventId";
	} else {
		$sql = "UPDATE events SET 
				name = '$name', 
				date = '$date', 
				startTime = '$startTime', 
				endTime = '$endTime', 
				location = '$location', 
				host = '$host', 
				description = '$description', 
				googleMapsLink = '$googleMapsLink', 
				guestList = '$guestList'
				WHERE id = $eventId";
	}
	
	// Update the event in the database
	if ($conn->query($sql)) {
		// Regenerate the events.json file to keep it in sync with the database
		generateEventsJSON($conn);
		
		echo json_encode(['success' => 'Event updated successfully.']);
	} else {
		http_response_code(500);
		echo json_encode(['error' => 'Failed to update the event: ' . $conn->error]);
	}
} else {
	http_response_code(405); // Method Not Allowed
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

// Update Events JSON file
generateEventsJSON($conn);



// Close the database connection
$conn->close();
