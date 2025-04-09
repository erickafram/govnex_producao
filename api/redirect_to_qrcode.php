<?php
// Configuração do CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Log de requisições
error_log("Solicitação para redirect_to_qrcode.php: " . json_encode($_GET));

// Verificar se foi fornecido um ID de transação
if (!isset($_GET['transaction_id'])) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        'error' => 'ID de transação não fornecido',
    ]);
    exit;
}

$transactionId = $_GET['transaction_id'];
$qrCodePath = __DIR__ . '/../temp/qrcode_' . $transactionId . '.png';

// Verificar se o arquivo existe
if (!file_exists($qrCodePath)) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        'error' => 'QR Code não encontrado',
        'path' => $qrCodePath,
        'transaction_id' => $transactionId
    ]);
    exit;
}

// Se o arquivo existe, redirecionar para a URL correta
// Usar caminho absoluto para evitar problemas
$serverName = $_SERVER['SERVER_NAME'];
$port = $_SERVER['SERVER_PORT'] != '80' ? ':' . $_SERVER['SERVER_PORT'] : '';
$basePath = $_SERVER['REQUEST_SCHEME'] . '://' . $serverName . $port;

// Determinar o caminho correto para o arquivo QR code
$qrCodeUrl = '/temp/qrcode_' . $transactionId . '.png';

// Redirecionar para o arquivo
header('Location: ' . $basePath . $qrCodeUrl); 