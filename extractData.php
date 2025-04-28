<?php
// filepath: /home/lilithe/public_html/events/extractData.php

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method. Only POST is allowed.']);
    exit;
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if the text field is provided
if (!isset($data['text']) || empty($data['text'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'The "text" field is required.']);
    exit;
}

// Prepare the payload for Ollama
$payload = [
    'model' => 'phi3',
    'prompt' => 'Extract the event name, date, start time, end time, description, and location from the following text:' . $data['text'],
    'temperature' => 0.7,
    'max_tokens' => 100,
    'stop' => ['\n', '###']
];

// Initialize cURL
$ch = curl_init('http://localhost:11434/api/generate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Transfer-Encoding: chunked'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// Execute the request and process the streaming response
$response = '';
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use ($response) {
    $response .= $chunk;
    return strlen($chunk);
});

curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors
if (curl_errno($ch)) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to connect to the Ollama API.', 'details' => curl_error($ch)]);
    curl_close($ch);
    exit;
}

// Close the cURL session
curl_close($ch);

// Process the streaming response
$lines = explode("\n", $response);
$finalResponse = '';
foreach ($lines as $line) {
    if (empty($line)) {
        continue;
    }

    $decodedLine = json_decode($line, true);
    if (isset($decodedLine['response'])) {
        $finalResponse .= $decodedLine['response'] . ' ';
    }

    if (isset($decodedLine['done']) && $decodedLine['done'] === true) {
        break;
    }
}

// Trim and parse the final response
$finalResponse = trim($finalResponse);
$result = json_decode($finalResponse, true);

// Check if the response is valid JSON
if ($result === null) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Invalid response from Ollama.', 'rawOutput' => $finalResponse]);
    exit;
}

// Return the parsed result
http_response_code(200);
echo json_encode($result);

