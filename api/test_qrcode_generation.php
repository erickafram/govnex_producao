<?php
// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Geração de QR Code</h1>";
echo "<p>Verificando requisitos...</p>";

// Verificar se o autoloader existe
$autoloaderPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloaderPath)) {
    echo "<p>✅ Autoloader encontrado: $autoloaderPath</p>";
} else {
    echo "<p>❌ Autoloader não encontrado em: $autoloaderPath</p>";
    echo "<p>Execute 'composer install' na raiz do projeto.</p>";
    exit;
}

// Incluir o autoloader do Composer
require_once $autoloaderPath;

// Verificar se as classes da biblioteca existem
echo "<h2>Verificando classes da biblioteca QR Code:</h2>";
$classesRequeridas = [
    'Endroid\QrCode\QrCode',
    'Endroid\QrCode\Writer\PngWriter'
];

// Detectar a versão da biblioteca pela presença de classes específicas
$isVersion3 = class_exists('Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh');
$isVersion2 = class_exists('Endroid\QrCode\ErrorCorrectionLevel');

echo "<p>Versão 3 da biblioteca: " . ($isVersion3 ? "✅ Detectada" : "❌ Não detectada") . "</p>";
echo "<p>Versão 2 da biblioteca: " . ($isVersion2 ? "✅ Detectada" : "❌ Não detectada") . "</p>";

$todasClassesExistem = true;
foreach ($classesRequeridas as $classe) {
    if (class_exists($classe)) {
        echo "<p>✅ Classe encontrada: $classe</p>";
    } else {
        echo "<p>❌ Classe não encontrada: $classe</p>";
        $todasClassesExistem = false;
    }
}

if (!$todasClassesExistem) {
    echo "<p>Algumas classes necessárias não foram encontradas. Execute 'composer require endroid/qr-code' na raiz do projeto.</p>";
    exit;
}

echo "<h2>Tentando gerar um QR Code simples:</h2>";

try {
    // Criar uma instância do QR Code
    $qrCode = new Endroid\QrCode\QrCode('Teste de QR Code');
    
    // Configurar o QR Code de acordo com a versão da biblioteca
    if ($isVersion3) {
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setErrorCorrectionLevel(new Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh());
        $writer = new Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);
        $qrCodeBase64 = base64_encode($result->getString());
    } else if ($isVersion2) {
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setErrorCorrectionLevel(Endroid\QrCode\ErrorCorrectionLevel::HIGH);
        $writer = new Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);
        $qrCodeBase64 = base64_encode($result->getString());
    } else {
        echo "<p>❌ Não foi possível determinar a versão da biblioteca.</p>";
        exit;
    }
    
    // Exibir o QR Code gerado
    echo "<p>✅ QR Code gerado com sucesso!</p>";
    echo "<img src='data:image/png;base64,$qrCodeBase64' alt='QR Code'>";
    
    // Tentar salvar o QR Code em um arquivo
    $tempDir = __DIR__ . '/../temp';
    if (!file_exists($tempDir)) {
        if (mkdir($tempDir, 0777, true)) {
            echo "<p>✅ Diretório temp criado com sucesso.</p>";
            chmod($tempDir, 0777);
        } else {
            echo "<p>❌ Não foi possível criar o diretório temp.</p>";
        }
    } else {
        echo "<p>✅ Diretório temp já existe.</p>";
    }
    
    $qrCodeFilename = 'test_qrcode_' . time() . '.png';
    $qrCodePath = $tempDir . '/' . $qrCodeFilename;
    
    if ($isVersion3) {
        if (file_put_contents($qrCodePath, $result->getString()) !== false) {
            echo "<p>✅ QR Code salvo com sucesso em: $qrCodePath</p>";
            chmod($qrCodePath, 0644);
            echo "<p>URL do QR Code: <a href='/temp/$qrCodeFilename' target='_blank'>/temp/$qrCodeFilename</a></p>";
        } else {
            echo "<p>❌ Não foi possível salvar o QR Code no arquivo.</p>";
        }
    } else if ($isVersion2) {
        if (file_put_contents($qrCodePath, $result->getString()) !== false) {
            echo "<p>✅ QR Code salvo com sucesso em: $qrCodePath</p>";
            chmod($qrCodePath, 0644);
            echo "<p>URL do QR Code: <a href='/temp/$qrCodeFilename' target='_blank'>/temp/$qrCodeFilename</a></p>";
        } else {
            echo "<p>❌ Não foi possível salvar o QR Code no arquivo.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao gerar o QR Code: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Informações do Servidor:</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Script Path: " . __FILE__ . "\n";
echo "</pre>"; 