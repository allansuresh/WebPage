<?php
set_time_limit(120); // Set to 120 seconds
ini_set('max_execution_time', 120); // Also set max_execution_time

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get raw POST data first
    $raw_data = file_get_contents('php://input');
    error_log("Raw POST data: " . $raw_data);

    // Decode JSON data
    $data = json_decode($raw_data, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    // Validate input
    $start = isset($data['start']) ? escapeshellarg($data['start']) : escapeshellarg('harry potter');
    $limit = isset($data['limit']) ? (int)$data['limit'] : 100;
    $limit = min($limit, 200); // Limit to a maximum of 200

    // Log the processed data
    error_log("Processed data - start: $start, limit: $limit");

    // Prepare the command to execute the Python script
    $scriptPath = escapeshellarg(__DIR__ . '/hp-fanfic-generate/app.py');
    $inputData = escapeshellarg(json_encode(['start' => $start, 'limit' => $limit]));
    $command = "python3 $scriptPath $inputData";

    // Execute the command and capture the output
    $output = shell_exec($command);

    // Check for execution errors
    if ($output === null) {
        throw new Exception("Failed to execute Python script.");
    }

    // Log the output
    error_log("Python script output: " . $output);

    // Decode the JSON output from the Python script
    $responseData = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON response from Python script: " . json_last_error_msg());
    }

    // Return the response
    echo json_encode([
        'success' => true,
        'story' => $responseData['story'] ?? '',
        'parameters' => $responseData['parameters'] ?? []
    ]);

} catch (Exception $e) {
    error_log("HP Generator Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'error_details' => error_get_last(),
            'php_version' => PHP_VERSION,
            'os' => PHP_OS
        ]
    ]);
}
?>
