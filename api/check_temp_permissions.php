<?php
// Definir headers para CORS e tipo de conteúdo
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Função para verificar e criar diretório se não existir
function checkAndCreateDir($path, $permissions = 0777) {
    $result = [
        'path' => $path,
        'exists' => false,
        'is_dir' => false,
        'is_writable' => false,
        'permissions' => 'N/A',
        'created' => false,
        'permissions_set' => false,
        'error' => null
    ];
    
    try {
        // Verificar se o caminho existe
        if (file_exists($path)) {
            $result['exists'] = true;
            $result['is_dir'] = is_dir($path);
            $result['is_writable'] = is_writable($path);
            $result['permissions'] = substr(sprintf('%o', fileperms($path)), -4);
            
            // Se não for um diretório, reportar erro
            if (!$result['is_dir']) {
                $result['error'] = 'O caminho existe mas não é um diretório';
                return $result;
            }
            
            // Tentar definir permissões se necessário
            if ($result['permissions'] !== sprintf('%04o', $permissions)) {
                if (chmod($path, $permissions)) {
                    $result['permissions'] = substr(sprintf('%o', fileperms($path)), -4);
                    $result['permissions_set'] = true;
                } else {
                    $result['error'] = 'Não foi possível definir as permissões';
                }
            } else {
                $result['permissions_set'] = true;
            }
        } else {
            // Criar diretório se não existir
            if (mkdir($path, $permissions, true)) {
                $result['exists'] = true;
                $result['is_dir'] = true;
                $result['created'] = true;
                
                // Definir permissões explicitamente para garantir
                chmod($path, $permissions);
                
                $result['is_writable'] = is_writable($path);
                $result['permissions'] = substr(sprintf('%o', fileperms($path)), -4);
                $result['permissions_set'] = true;
            } else {
                $result['error'] = 'Não foi possível criar o diretório';
            }
        }
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
    }
    
    return $result;
}

// Verificar e criar diretório de logs
$logsDir = __DIR__ . '/logs';
$logsResult = checkAndCreateDir($logsDir);

// Função para log
function logMessage($message) {
    global $logsDir;
    $logFile = $logsDir . '/temp_check.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

logMessage("Verificando diretório temp");

// Verificar e corrigir permissões do diretório temp
$tempDir = __DIR__ . '/../temp';
$tempResult = checkAndCreateDir($tempDir);

logMessage("Resultado da verificação do diretório temp: " . json_encode($tempResult));

// Listar arquivos no diretório temp
$files = [];
if ($tempResult['exists'] && $tempResult['is_dir']) {
    try {
        $dirFiles = scandir($tempDir);
        foreach ($dirFiles as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $tempDir . '/' . $file;
            $files[] = [
                'name' => $file,
                'is_dir' => is_dir($filePath),
                'size' => filesize($filePath),
                'permissions' => substr(sprintf('%o', fileperms($filePath)), -4),
                'is_readable' => is_readable($filePath),
                'is_writable' => is_writable($filePath)
            ];
        }
    } catch (Exception $e) {
        logMessage("Erro ao listar arquivos: " . $e->getMessage());
    }
}

// Verificar permissões do diretório raiz
$rootDir = dirname($tempDir);
$rootResult = [
    'path' => $rootDir,
    'exists' => file_exists($rootDir),
    'is_dir' => is_dir($rootDir),
    'is_writable' => is_writable($rootDir),
    'permissions' => is_dir($rootDir) ? substr(sprintf('%o', fileperms($rootDir)), -4) : 'N/A'
];

// Preparar resposta
$response = [
    'success' => $tempResult['exists'] && $tempResult['is_dir'] && $tempResult['is_writable'],
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown'
    ],
    'logs_directory' => $logsResult,
    'temp_directory' => $tempResult,
    'root_directory' => $rootResult,
    'files_in_temp' => $files,
    'action_required' => !$tempResult['exists'] || !$tempResult['is_writable']
];

// Retornar resultados como JSON
echo json_encode($response, JSON_PRETTY_PRINT); 