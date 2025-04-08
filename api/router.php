<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Corrigir a URI se tiver /api no começo
$requestUri = $_SERVER['REQUEST_URI'];
$cleanedUri = preg_replace('/^\/api/', '', $requestUri); // remove prefixo /api
$file = __DIR__ . $cleanedUri;

if (file_exists($file) && is_file($file)) {
    include $file;
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Arquivo não encontrado']);
