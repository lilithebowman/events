<?php
// filepath: /home/lilithe/public_html/events/ollamaStats.php

// Set the content type to JSON
header('Content-Type: application/json');

// Fetch data from the API
$apiUrl = 'http://localhost:11434/api/ps';
$response = file_get_contents($apiUrl);

// Check if the API request was successful
if ($response === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to fetch data from the API.']);
    exit;
}

// Decode the JSON response
$data = json_decode($response, true);

// Check if the response contains the "models" key
if (!isset($data['models']) || !is_array($data['models'])) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Invalid API response format.']);
    exit;
}

// Extract the "expires_at" field and calculate the time remaining
$timeRemaining = [];
foreach ($data['models'] as $model) {
    if (isset($model['expires_at'])) {
        $expiresAt = $model['expires_at'];
        $expiresAtTimestamp = strtotime($expiresAt);

        if ($expiresAtTimestamp !== false) {
            $currentTimestamp = time();
            $remainingSeconds = $expiresAtTimestamp - $currentTimestamp;

            if ($remainingSeconds > 0) {
                $timeRemaining[] = [
                    'model' => $model['name'],
                    'expires_at' => $expiresAt,
                    'time_remaining' => gmdate('H:i:s', $remainingSeconds) // Format as HH:MM:SS
                ];
            }
        }
    }
}

// Check if any time remaining was found
if (empty($timeRemaining)) {
    echo json_encode(['timeRemaining' => '0']);
    exit;
}

// Return the time remaining
http_response_code(200);
echo json_encode(['timeRemaining' => $timeRemaining]);
