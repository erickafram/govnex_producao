<?php
// Ensure all errors are caught and returned as JSON, not as HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

// First load CORS and other requirements with absolute paths
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db_config.php';

// Set JSON content type early
header("Content-Type: application/json");

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Log for debugging
$logFile = __DIR__ . '/login_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login request received\n", FILE_APPEND);

try {
    // Add CORS headers
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    // Handle OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        sendJsonResponse(['status' => 'success'], 200);
    }

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(['error' => 'Method not allowed'], 405);
    }

    // Get request data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Log received data
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Data received: " . json_encode($data) . "\n", FILE_APPEND);

    // Validate data
    if (!is_array($data) || empty($data['email']) || empty($data['password'])) {
        sendJsonResponse(['error' => 'Email and password are required'], 400);
    }

    // Get database connection
    $conn = getDbConnection();
    if (!$conn) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Error: Database connection failed\n", FILE_APPEND);
        sendJsonResponse(['error' => 'Internal server error - DB connection failed'], 500);
    }

    // Find user by email
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user exists
    if (!$user) {
        sendJsonResponse(['error' => 'Invalid credentials'], 401);
    }
    
    // Verify password
    if (!password_verify($data['password'], $user['senha'])) {
        sendJsonResponse(['error' => 'Invalid credentials'], 401);
    }
    
    // Generate token
    $token = bin2hex(random_bytes(32));
    
    // Remove password from result
    unset($user['senha']);
    
    // Add isAdmin property
    $user['isAdmin'] = ($user['nivel_acesso'] === 'administrador');
    
    // Log successful login
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login successful for: " . $data['email'] . "\n", FILE_APPEND);
    
    // Send success response
    sendJsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token
    ], 200);
    
} catch (PDOException $e) {
    // Log database error
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Database error: " . $e->getMessage() . "\n", FILE_APPEND);
    sendJsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    // Log general error
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - General error: " . $e->getMessage() . "\n", FILE_APPEND);
    sendJsonResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
}
