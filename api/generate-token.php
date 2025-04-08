<?php
// Ensure errors are logged but not displayed
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/db_config.php';

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Log function for debugging
function log_message($message) {
    $logFile = __DIR__ . '/generate_token_log.txt';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

log_message("Token generation request received");

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Get database connection
    $db = getDbConnection();
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    log_message("Database connection successful");

    // Check if table exists and create if needed
    try {
        $checkTable = $db->query("SHOW TABLES LIKE 'api_tokens'");
        if ($checkTable->rowCount() === 0) {
            log_message("Creating api_tokens table");
            $sql = "
                CREATE TABLE IF NOT EXISTS api_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    token VARCHAR(191) NOT NULL,
                    description VARCHAR(255) NULL,
                    user_id INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at TIMESTAMP NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    UNIQUE KEY (token)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            $db->exec($sql);
            log_message("Table created successfully");
        }
    } catch (PDOException $e) {
        log_message("Error checking/creating table: " . $e->getMessage());
        // Continue anyway - we'll try to insert
    }

    // Generate unique token
    $token = bin2hex(random_bytes(32));
    log_message("Generated token: " . substr($token, 0, 8) . "...");

    // Insert token into database
    $description = "Token generated via simple API";
    $stmt = $db->prepare("INSERT INTO api_tokens (token, description, is_active) VALUES (:token, :description, TRUE)");
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':description', $description);
    $stmt->execute();
    log_message("Token inserted into database");

    echo json_encode([
        "success" => true, 
        "token" => $token, 
        "message" => "Token generated successfully"
    ]);
    
} catch (PDOException $e) {
    log_message("Database error: " . $e->getMessage());
    error_log("Generate token API database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => "Database error", 
        "details" => $e->getMessage()
    ]);
} catch (Exception $e) {
    log_message("General error: " . $e->getMessage());
    error_log("Generate token API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => "Server error", 
        "details" => $e->getMessage()
    ]);
} 
