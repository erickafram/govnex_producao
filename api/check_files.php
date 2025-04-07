<?php
// Adicionar cabeçalhos CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Verificar arquivos importantes
$apiDir = __DIR__;
$files = [
    'login.php',
    'update_profile.php',
    'update_password.php',
    'get_users_list.php',
    'router.php',
    'cors.php'
];

$result = [
    'server' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Desconhecido',
        'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Desconhecido',
        'api_dir' => $apiDir,
        'current_dir' => getcwd()
    ],
    'files' => []
];

// Verificar cada arquivo
foreach ($files as $file) {
    $filePath = $apiDir . '/' . $file;
    $result['files'][$file] = [
        'exists' => file_exists($filePath),
        'readable' => is_readable($filePath),
        'size' => file_exists($filePath) ? filesize($filePath) : 0,
        'path' => $filePath
    ];
}

// Listar todos os arquivos na pasta api
$allFiles = scandir($apiDir);
$result['directory'] = [
    'path' => $apiDir,
    'files' => $allFiles
];

// Verificar permissões da pasta
$result['permissions'] = [
    'api_dir' => substr(sprintf('%o', fileperms($apiDir)), -4),
    'parent_dir' => substr(sprintf('%o', fileperms(dirname($apiDir))), -4)
];

// Retornar resultado
echo json_encode($result, JSON_PRETTY_PRINT);
