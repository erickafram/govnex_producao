<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Conexão com o banco de dados estabelecida com sucesso',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Falha na conexão com o banco de dados',
        'debug_message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}