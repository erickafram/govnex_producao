<?php
// Habilitar CORS para permitir requisições de diferentes origens
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

// Se for uma requisição OPTIONS, retornar apenas os cabeçalhos
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Informações sobre o servidor
$serverInfo = [
    'status' => 'success',
    'message' => 'API está funcionando corretamente',
    'server' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Desconhecido',
        'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Desconhecido',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Desconhecido',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'Desconhecido',
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'Desconhecido',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido',
    ],
    'files' => []
];

// Verificar se os arquivos importantes existem
$apiFiles = [
    'login.php',
    'update_profile.php',
    'update_password.php',
    'get_users_list.php',
    'config/database.php'
];

foreach ($apiFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    $serverInfo['files'][$file] = [
        'exists' => file_exists($fullPath),
        'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
        'permissions' => file_exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'N/A',
        'path' => $fullPath
    ];
}

// Verificar conexão com o banco de dados
try {
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    $serverInfo['database'] = [
        'connected' => true,
        'message' => 'Conexão com o banco de dados estabelecida com sucesso'
    ];
} catch (Exception $e) {
    $serverInfo['database'] = [
        'connected' => false,
        'message' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()
    ];
}

// Retornar as informações em formato JSON
echo json_encode($serverInfo, JSON_PRETTY_PRINT);
