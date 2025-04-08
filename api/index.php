<?php
// Configurar cabeçalhos CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Responder com erro 404 em formato JSON
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Endpoint não encontrado',
    'message' => 'A API solicitada não existe'
]);
