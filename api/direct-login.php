<?php
// Ensure all errors are caught and returned as JSON, not as HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0777, true);
    chmod($logsDir, 0777); // Ensure directory is writable
}

// Log file for debugging
$logFile = $logsDir . '/login_log.txt';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Log request details
logMessage("Request received");
logMessage("Request Method: " . $_SERVER['REQUEST_METHOD']);
logMessage("Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set'));
logMessage("Raw input: " . file_get_contents('php://input'));

// First load database connection
require_once __DIR__ . '/config/database.php';

// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 3600");
header("Content-Type: application/json; charset=UTF-8");

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    // Handle OPTIONS request (preflight)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        logMessage("Handling OPTIONS request");
        sendJsonResponse(['status' => 'success'], 200);
    }

    // Ensure this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logMessage("Invalid method: " . $_SERVER['REQUEST_METHOD']);
        sendJsonResponse(['error' => 'Method not allowed'], 405);
    }

    // Get and parse request data
    $input = file_get_contents('php://input');
    logMessage("Received input: " . $input);
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logMessage("JSON decode error: " . json_last_error_msg());
        sendJsonResponse(['error' => 'Invalid JSON data'], 400);
    }

    // Validate required fields
    if (!is_array($data) || empty($data['email']) || empty($data['password'])) {
        logMessage("Missing required fields");
        sendJsonResponse(['error' => 'Email and password are required'], 400);
    }

    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        logMessage("Database connection failed");
        sendJsonResponse(['error' => 'Internal server error - DB connection failed'], 500);
    }

    // Find user by email
    $stmt = $conn->prepare("
        SELECT 
            id,
            nome as name,
            email,
            senha as password,
            telefone as phone,
            COALESCE(cpf, cnpj) as document,
            dominio as domain,
            credito as balance,
            nivel_acesso as access_level,
            data_cadastro as created_at
        FROM usuarios 
        WHERE email = :email
    ");
    
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user exists
    if (!$user) {
        logMessage("User not found: " . $data['email']);
        sendJsonResponse(['error' => 'Invalid credentials'], 401);
    }
    
    // Verify password
    if (!password_verify($data['password'], $user['password'])) {
        logMessage("Invalid password for user: " . $data['email']);
        sendJsonResponse(['error' => 'Invalid credentials'], 401);
    }
    
    // Generate token
    $token = bin2hex(random_bytes(32));
    
    // Store token in database
    $stmt = $conn->prepare("
        INSERT INTO sessoes (usuario_id, token, expiracao)
        VALUES (:usuario_id, :token, DATE_ADD(NOW(), INTERVAL 7 DAY))
    ");
    
    $stmt->bindParam(':usuario_id', $user['id']);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    // Remove password from result
    unset($user['password']);
    
    // Add isAdmin property
    $user['isAdmin'] = ($user['access_level'] === 'administrador');
    unset($user['access_level']);
    
    // Format response data
    $responseData = [
        'success' => true,
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token
    ];
    
    logMessage("Login successful for user: " . $data['email']);
    sendJsonResponse($responseData, 200);
    
} catch (PDOException $e) {
    logMessage("Database error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Internal server error - Database error'], 500);
} catch (Exception $e) {
    logMessage("General error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Internal server error'], 500);
}