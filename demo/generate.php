<?php
set_time_limit(120); // Set to 120 seconds
ini_set('max_execution_time', 120); // Also set max_execution_time
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Return only the headers and not the content
    exit(0);
}

// Set the content type to JSON
header('Content-Type: application/json');

try {
    // Get raw POST data first
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
    
    // Add timeout to Python execution
    $timeLimit = 110; // Leave some buffer for PHP processing
    
    // Construct command based on OS
    if (PHP_OS !== 'WINNT') {
        $command = sprintf('timeout %d python "%s" %s %d 2>&1', 
            $timeLimit,
            $pythonScript, 
            escapeshellarg($start), 
            $limit
        );
    } else {
        $command = sprintf('python "%s" %s %d 2>&1', 
            $pythonScript, 
            escapeshellarg($start), 
            $limit
        );
    }
    
    error_log("Executing command: " . $command);
    
    // Execute with timeout check
    $startTime = time();
    $output = [];
    $fullOutput = '';
    
    // Execute using proc_open for better control
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin
        1 => array("pipe", "w"),  // stdout
        2 => array("pipe", "w")   // stderr
    );
    
    $process = proc_open($command, $descriptorspec, $pipes);
    
    if (is_resource($process)) {
        // Close stdin as we don't need it
        fclose($pipes[0]);
        
        // Read output while checking timeout
        while (!feof($pipes[1])) {
            if (time() - $startTime > $timeLimit) {
                proc_terminate($process);
                throw new Exception("Execution time limit exceeded");
            }
            
            $fullOutput .= fgets($pipes[1]);
        }
        
        // Get error output
        $errorOutput = stream_get_contents($pipes[2]);
        
        // Close pipes
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        // Close process
        $returnVar = proc_close($process);
        
        // Log results
        error_log("Return Status: " . $returnVar);
        error_log("Command Output: " . $fullOutput);
        error_log("Error Output: " . $errorOutput);
        
        if ($returnVar !== 0) {
            throw new Exception("Python script failed with status: " . $returnVar . "\nOutput: " . $fullOutput . "\nError: " . $errorOutput);
        }
        
        // Process output
        $output = array_filter(explode("\n", $fullOutput));
        array_shift($output); // Remove the first line if it's a progress message
    } else {
        throw new Exception("Failed to execute command");
    }
    
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