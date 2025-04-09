<?php
// Incluir o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Importar as classes necessárias
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;

// Configurar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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
$pixCode = $_GET['pix_code'];

// Verificar o código PIX
if (empty($pixCode)) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        'error' => 'Código PIX vazio'
    ]);
    exit;
}

try {
    // Criar o QR Code
    $qrCode = new QrCode($pixCode);
    $qrCode->setSize(300);
    $qrCode->setMargin(10);
    $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
    
    // Gerar o QR Code
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    // Também salvar em arquivo
    $tempDir = __DIR__ . '/../temp';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
        chmod($tempDir, 0777);
    }
    
    $qrPath = $tempDir . '/qrcode_' . $transactionId . '.png';
    file_put_contents($qrPath, $result->getString());
    chmod($qrPath, 0644);
    
    // Definir os cabeçalhos e enviar a imagem
    header("Content-Type: " . $result->getMimeType());
    echo $result->getString();
    
} catch (Exception $e) {
    // Se ocorrer um erro, retornar uma mensagem de erro
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        'error' => 'Erro ao gerar QR Code: ' . $e->getMessage()
    ]);
} $e->getMessage()
    ]);
} 