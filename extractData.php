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

// Prepare the command to send the text to Ollama
$text = escapeshellarg($data['text']);
$command = "echo $text | ollama query extractEventDetails";

// Execute the command
$output = shell_exec($command);

// Check if the command execution was successful
if ($output === null) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to execute the command to query Ollama.']);
    exit;
}

// Parse the output from Ollama
$result = json_decode($output, true);

// Check if the output is valid JSON
if ($result === null) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Invalid response from Ollama.', 'rawOutput' => $output]);
    exit;
}

// Return the parsed result
http_response_code(200);
echo json_encode($result);
