<?php
// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0777, true);
    chmod($logsDir, 0777);
}

// Log file for testing
$logFile = $logsDir . '/connection_test.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Start test
logMessage("Starting database connection test");
logMessage("Server info: " . json_encode($_SERVER));

// Load database configuration
require_once __DIR__ . '/config/database.php';

try {
    // Get database connection
    $database = new Database();
    logMessage("Database object created");
    
    $conn = $database->getConnection();
    logMessage("Connection obtained successfully");
    
    // Test query
    $stmt = $conn->query("SELECT VERSION() as version");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    logMessage("MySQL version: " . $result['version']);
    
    // Test basic table existence
    $tablesQuery = $conn->query("SHOW TABLES");
    $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);
    logMessage("Tables found: " . implode(', ', $tables));
    
    // Output success
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'mysql_version' => $result['version'],
        'tables_found' => count($tables),
        'tables' => $tables
    ]);
    
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
    
    // Output error
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
} 