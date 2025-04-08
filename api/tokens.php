<?php
// Ensure errors are logged but not displayed
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/db_config.php';

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Log function for debugging
function log_message($message) {
    $logFile = __DIR__ . '/tokens_log.txt';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

log_message("Request received: " . $_SERVER['REQUEST_METHOD']);

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get database connection
try {
    $db = getDbConnection();
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    log_message("Database connection successful");
} catch (Exception $e) {
    log_message("Database connection error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection error"]);
    exit;
}

try {
    // Check if table exists
    try {
        $checkTable = $db->query("SHOW TABLES LIKE 'api_tokens'");
        if ($checkTable->rowCount() === 0) {
            // Create table if it doesn't exist
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
        // Continue anyway - we'll create the table if needed in each request handler
    }

    // GET: List tokens
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        log_message("Processing GET request to list tokens");
        $tokens = [];
        try {
            $stmt = $db->query("
                SELECT t.id, t.token, t.description, t.user_id, 
                       u.nome as user_name, 
                       t.created_at, t.expires_at, t.is_active 
                FROM api_tokens t
                LEFT JOIN usuarios u ON t.user_id = u.id 
                ORDER BY t.created_at DESC
            ");
            
            if ($stmt !== false) {
                $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Convert IDs to string and is_active to boolean
                foreach ($tokens as &$token) {
                    $token['id'] = (string)$token['id'];
                    // Ensure is_active is a real boolean (1/0 => true/false)
                    $token['is_active'] = $token['is_active'] == 1;
                    $token['user_id'] = $token['user_id'] ? (string)$token['user_id'] : null;
                    $token['userName'] = $token['user_name'] ?? null;
                    unset($token['user_name']); // Remove duplicate field
                }
            }
            
            log_message("Retrieved " . count($tokens) . " tokens");
            echo json_encode(["success" => true, "tokens" => $tokens]);
        } catch (PDOException $e) {
            log_message("Error fetching tokens: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Error fetching tokens"]);
        }
        exit;
    }
    
    // POST: Generate new token
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        log_message("Processing POST request to generate token");
        // Read JSON data from request body
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        log_message("Request data: " . $input);
        
        // Extract data
        $description = isset($data['description']) ? $data['description'] : null;
        $userId = isset($data['userId']) && !empty($data['userId']) ? $data['userId'] : null;
        $expiresAt = isset($data['expiresAt']) && !empty($data['expiresAt']) ? $data['expiresAt'] : null;
        
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        
        try {
            // Insert into database
            $sql = "
                INSERT INTO api_tokens (token, description, user_id, expires_at, is_active) 
                VALUES (:token, :description, :user_id, :expires_at, TRUE)
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->execute();
            
            // Get inserted token
            $id = $db->lastInsertId();
            $stmt = $db->prepare("SELECT id, token, description, user_id, created_at, expires_at, is_active FROM api_tokens WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tokenData) {
                $tokenData['id'] = (string)$tokenData['id'];
                // Ensure is_active is a real boolean
                $tokenData['is_active'] = $tokenData['is_active'] == 1;
                $tokenData['user_id'] = $tokenData['user_id'] ? (string)$tokenData['user_id'] : null;
            }
            
            log_message("Token generated successfully: " . substr($token, 0, 8) . "...");
            echo json_encode(["success" => true, "token" => $tokenData]);
        } catch (PDOException $e) {
            log_message("Error generating token: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Error generating token"]);
        }
        exit;
    }
    
    // PUT: Update token status
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        log_message("Processing PUT request to update token");
        // Check ID
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            log_message("Invalid token ID: " . $id);
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Token ID required"]);
            exit;
        }
        
        // Read JSON data
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        log_message("PUT data: " . $input);
        
        // Check data
        if (!isset($data['isActive'])) {
            log_message("isActive not provided in request");
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Token status not provided"]);
            exit;
        }
        
        $isActive = (bool)$data['isActive'];
        
        try {
            // Update status
            $stmt = $db->prepare("UPDATE api_tokens SET is_active = :is_active WHERE id = :id");
            $stmt->bindValue(':is_active', $isActive, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                log_message("Token " . $id . " updated to " . ($isActive ? "active" : "inactive"));
                echo json_encode(["success" => true, "message" => $isActive ? "Token activated" : "Token deactivated"]);
            } else {
                log_message("Token " . $id . " not found");
                http_response_code(404);
                echo json_encode(["success" => false, "error" => "Token not found"]);
            }
        } catch (PDOException $e) {
            log_message("Error updating token: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Error updating token"]);
        }
        exit;
    }
    
    // Unsupported method
    log_message("Unsupported method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
    
} catch (PDOException $e) {
    log_message("Database error: " . $e->getMessage());
    error_log("Token API database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database error", "details" => $e->getMessage()]);
} catch (Exception $e) {
    log_message("General error: " . $e->getMessage());
    error_log("Token API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error", "details" => $e->getMessage()]);
} 
