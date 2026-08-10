<?php
// File: api.php

header('Content-Type: application/json');

$jsonFile = 'servers.json';

// Check if the file exists
if (!file_exists($jsonFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Server list not found.']);
    exit;
}

// Load the JSON data
$servers = json_decode(file_get_contents($jsonFile), true);

// Get 'name' parameter from query
if (!isset($_GET['name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing name parameter.']);
    exit;
}

$name = $_GET['name'];

// Check if the name exists in the list
if (!array_key_exists($name, $servers)) {
    http_response_code(404);
    echo json_encode(['error' => 'Domain not found.']);
    exit;
}

// Return the domain
echo json_encode([
    'name' => $name,
    'url' => $servers[$name]
]);