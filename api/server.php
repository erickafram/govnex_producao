<?php
/**
 * Servidor PHP com suporte a CORS para desenvolvimento e produção
 * Execute este arquivo com: php server.php
 */

// Definir porta
$port = 8000;
$host = '0.0.0.0';

// Mensagem de início
echo "Iniciando servidor PHP na porta {$port}...\n";
echo "Acesse: http://localhost:{$port}\n";
echo "Pressione Ctrl+C para parar o servidor.\n";

// Registrar manipulador de requisições
$requestHandler = function ($request, $response) {
    // Adicionar cabeçalhos CORS a todas as respostas
    $response->setHeader('Access-Control-Allow-Origin', '*');
    $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    $response->setHeader('Access-Control-Max-Age', '3600');
    $response->setHeader('Access-Control-Allow-Credentials', 'true');
    
    // Responder imediatamente às solicitações OPTIONS
    if ($request->getMethod() === 'OPTIONS') {
        $response->setStatusCode(200);
        return $response;
    }
    
    // Log da requisição
    $logFile = __DIR__ . '/api_log.txt';
    $logMessage = date('Y-m-d H:i:s') . " - " . $request->getMethod() . " " . $request->getUri() . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    return false; // Continuar com o processamento normal
};

// Comando para iniciar o servidor
$command = sprintf(
    'php -S %s:%d -t %s',
    $host,
    $port,
    __DIR__
);

// Executar o servidor
passthru($command);
