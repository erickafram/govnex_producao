<?php
// Configurar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Função para fazer log
function logMessage($message) {
    $logFile = __DIR__ . '/logs/qrcode_external.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Verificar se foi fornecido um ID de transação e um código PIX
if (!isset($_GET['transaction_id']) || !isset($_GET['pix_code'])) {
    // Se não houver ID ou código, retornar um erro
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        'error' => 'Parâmetros obrigatórios não fornecidos',
        'required' => ['transaction_id', 'pix_code']
    ]);
    exit;
}

// Obter os parâmetros
$transactionId = $_GET['transaction_id'];
$pixCode = urlencode($_GET['pix_code']);

// Verificar o código PIX
if (empty($pixCode)) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        'error' => 'Código PIX vazio'
    ]);
    exit;
}

try {
    // Usar Google Charts API para gerar o QR code
    $size = 300;
    $qrCodeUrl = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$pixCode}&choe=UTF-8";
    
    logMessage("Usando URL externo: $qrCodeUrl");
    
    // Tentar obter a imagem do serviço externo
    $qrCodeImage = file_get_contents($qrCodeUrl);
    
    if ($qrCodeImage === false) {
        throw new Exception("Não foi possível obter a imagem do QR code do serviço externo");
    }
    
    // Também salvar em arquivo
    $tempDir = __DIR__ . '/../temp';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
        chmod($tempDir, 0777);
    }
    
    $qrPath = $tempDir . '/qrcode_' . $transactionId . '.png';
    if (file_put_contents($qrPath, $qrCodeImage) === false) {
        logMessage("Erro ao salvar imagem em: $qrPath");
    } else {
        chmod($qrPath, 0644);
        logMessage("Imagem salva com sucesso em: $qrPath");
    }
    
    // Definir os cabeçalhos e enviar a imagem
    header("Content-Type: image/png");
    echo $qrCodeImage;
    
} catch (Exception $e) {
    logMessage("Erro: " . $e->getMessage());
    // Se ocorrer um erro, retornar uma mensagem de erro
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        'error' => 'Erro ao gerar QR Code: ' . $e->getMessage()
    ]);
} 