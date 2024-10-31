<?php
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
    // Log the raw POST data
    $raw_data = file_get_contents('php://input');
    error_log("Raw POST data: " . $raw_data);

    // Get POST data
    $data = json_decode($raw_data, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // Validate input
    $start = $data['start'] ?? 'harry potter';
    $limit = min((int)($data['limit'] ?? 100), 200);
    
    // Log the processed data
    error_log("Processed data - start: $start, limit: $limit");
    
    // Get absolute directory path
    $dir = dirname(__FILE__) . '/hp-fanfic-generate';
    $pythonScript = $dir . '/fanfic.py';
    
    // Log directory information
    error_log("Working Directory: " . getcwd());
    error_log("Script Directory: " . $dir);
    error_log("Python Script Path: " . $pythonScript);
    
    // Check if Python script exists
    if (!file_exists($pythonScript)) {
        throw new Exception("Python script not found at: $pythonScript");
    }
    
    // Get Python version
    $pythonVersion = shell_exec('python --version 2>&1');
    error_log("Python Version: " . $pythonVersion);
    
    // Construct command
    $command = sprintf('python "%s" %s %d 2>&1', $pythonScript, escapeshellarg($start), $limit);
    error_log("Executing command: " . $command);
    
    // Execute Python script
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);
    
    // Log the complete output
    error_log("Return Status: " . $returnVar);
    error_log("Command Output: " . print_r($output, true));
    
    if ($returnVar !== 0) {
        throw new Exception("Python script failed with status: " . $returnVar . "\nOutput: " . implode("\n", $output));
    }

    array_shift($output);
    
    // Return response
    echo json_encode([
        'success' => true,
        'story' => implode("\n", $output),
        'debug' => [
            'command' => $command,
            'working_dir' => getcwd(),
            'python_script' => $pythonScript,
            'python_version' => $pythonVersion,
            'return_status' => $returnVar,
            'raw_output' => $output
        ]
    ]);

} catch (Exception $e) {
    error_log("HP Generator Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'dir' => $dir ?? null,
            'python_script' => $pythonScript ?? null,
            'command' => $command ?? null,
            'error_details' => error_get_last(),
            'php_version' => PHP_VERSION,
            'os' => PHP_OS
        ]
    ]);
}
?>