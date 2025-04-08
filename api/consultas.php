<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/config/database.php';

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

// Log file for debugging
$logFile = __DIR__ . '/logs/consultas_log.txt';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get query parameters
    $userId = $_GET['userId'] ?? null;
    $dominio = $_GET['dominio'] ?? null;

    if (!$userId) {
        throw new Exception('UserId is required');
    }

    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();

    // Get user data
    $stmt = $conn->prepare("
        SELECT credito, nivel_acesso 
        FROM usuarios 
        WHERE id = :userId
    ");
    
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        throw new Exception('User not found');
    }

    // Return response
    echo json_encode([
        'success' => true,
        'consultasDisponiveis' => 100, // Você pode ajustar este valor conforme necessário
        'credito' => $userData['credito']
    ]);

} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
