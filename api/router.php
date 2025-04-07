<?php
// Adicionar cabeçalhos CORS a todas as requisições
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");

// Log para depuração
$logFile = __DIR__ . '/cors_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Requisição recebida: " . $_SERVER['REQUEST_URI'] . " - Método: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);

// Responder imediatamente às solicitações OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Respondendo OPTIONS com 200\n", FILE_APPEND);
    exit;
}

// Obter o caminho da requisição
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Caminho da requisição: " . $requestPath . "\n", FILE_APPEND);

// Verificar se é uma requisição para a API
if (strpos($requestPath, '/api/') === 0) {
    // Remover o prefixo /api
    $filePath = __DIR__ . substr($requestPath, 4);
} else {
    // Caminho relativo dentro da pasta api
    $filePath = __DIR__ . '/' . ltrim($requestPath, '/');
}

file_put_contents($logFile, date('Y-m-d H:i:s') . " - Procurando arquivo: " . $filePath . "\n", FILE_APPEND);

// Listar todos os arquivos na pasta api para debug
$files = scandir(__DIR__);
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Arquivos disponíveis: " . implode(", ", $files) . "\n", FILE_APPEND);

// Verificar se o arquivo existe
if (file_exists($filePath) && is_file($filePath)) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Arquivo encontrado, incluindo: " . $filePath . "\n", FILE_APPEND);
    
    // Incluir o arquivo
    include $filePath;
    exit;
} else {
    // Tentar encontrar o arquivo diretamente na pasta api
    $directFile = __DIR__ . '/' . basename($requestPath);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Tentando arquivo direto: " . $directFile . "\n", FILE_APPEND);
    
    if (file_exists($directFile) && is_file($directFile)) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Arquivo direto encontrado, incluindo: " . $directFile . "\n", FILE_APPEND);
        include $directFile;
        exit;
    }
}

// Arquivo não encontrado
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Arquivo não encontrado: " . $filePath . "\n", FILE_APPEND);
http_response_code(404);
echo json_encode(['error' => 'Arquivo não encontrado']);
