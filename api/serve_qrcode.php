<?php
// Configuração do CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Log de requisições
error_log("Solicitação para serve_qrcode.php: " . json_encode($_GET));

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

// Verificar permissões do arquivo
$permissions = substr(sprintf('%o', fileperms($qrCodePath)), -4);
error_log("Permissões do arquivo QR Code: " . $permissions);

// Se o arquivo existe, servir diretamente a imagem
header('Content-Type: image/png');
header('Content-Length: ' . filesize($qrCodePath));
readfile($qrCodePath); 