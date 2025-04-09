<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/html; charset=UTF-8");

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0777, true);
    chmod($logsDir, 0777);
}

// Log file for testing
$logFile = $logsDir . '/qrcode_html_test.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Start test
logMessage("QR Code HTML test page called");
logMessage("Server info: " . json_encode($_SERVER));

// Check if temp directory exists
$tempDir = __DIR__ . '/../temp';
$webTempDir = '/temp';
$qrCodeFiles = [];

if (is_dir($tempDir)) {
    logMessage("Temp directory exists at: $tempDir");
    
    // List QR code files
    $files = scandir($tempDir);
    foreach ($files as $file) {
        if (strpos($file, 'qrcode_') === 0) {
            $qrCodeFiles[] = [
                'filename' => $file,
                'url' => "{$webTempDir}/{$file}",
                'absolute_url' => "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "{$webTempDir}/{$file}",
                'full_path' => "{$tempDir}/{$file}",
                'exists' => file_exists("{$tempDir}/{$file}"),
                'size' => filesize("{$tempDir}/{$file}"),
                'permissions' => substr(sprintf('%o', fileperms("{$tempDir}/{$file}")),-4),
            ];
        }
    }
    
    logMessage("Found " . count($qrCodeFiles) . " QR code files");
} else {
    logMessage("Temp directory does not exist at: $tempDir");
}

// Create HTML page
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de QR Codes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .container {
            margin-top: 20px;
        }
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .qr-item {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .qr-img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 10px;
        }
        .server-info {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .test-section {
            margin-top: 40px;
        }
        .test-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
</head>
<body>
    <h1>Teste de QR Codes</h1>
    
    <div class="server-info">
        <h2>Informações do Servidor</h2>
        <p><strong>Nome do Servidor:</strong> <?php echo $_SERVER['SERVER_NAME'] ?? 'Desconhecido'; ?></p>
        <p><strong>Endereço IP:</strong> <?php echo $_SERVER['SERVER_ADDR'] ?? 'Desconhecido'; ?></p>
        <p><strong>Porta:</strong> <?php echo $_SERVER['SERVER_PORT'] ?? 'Desconhecida'; ?></p>
        <p><strong>HTTP Host:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'Desconhecido'; ?></p>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Desconhecido'; ?></p>
        <p><strong>Diretório Temp:</strong> <?php echo $tempDir; ?></p>
        <p><strong>Diretório Temp Existe:</strong> <?php echo is_dir($tempDir) ? 'Sim' : 'Não'; ?></p>
        <p><strong>Diretório Temp Permissões:</strong> <?php echo is_dir($tempDir) ? substr(sprintf('%o', fileperms($tempDir)),-4) : 'N/A'; ?></p>
    </div>
    
    <div class="container">
        <h2>Arquivos de QR Code (<?php echo count($qrCodeFiles); ?> encontrados)</h2>
        
        <?php if (count($qrCodeFiles) > 0): ?>
            <div class="qr-grid">
                <?php foreach ($qrCodeFiles as $file): ?>
                    <div class="qr-item">
                        <img src="<?php echo $file['url']; ?>" alt="<?php echo $file['filename']; ?>" class="qr-img">
                        <p><strong>Arquivo:</strong> <?php echo $file['filename']; ?></p>
                        <p><strong>Tamanho:</strong> <?php echo $file['size']; ?> bytes</p>
                        <p><strong>Permissões:</strong> <?php echo $file['permissions']; ?></p>
                        <p><strong>URL:</strong> <a href="<?php echo $file['url']; ?>" target="_blank"><?php echo $file['url']; ?></a></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Nenhum arquivo de QR code encontrado no diretório temp.</p>
        <?php endif; ?>
    </div>
    
    <div class="test-section">
        <h2>Teste de URL Absoluta vs Relativa</h2>
        
        <?php if (count($qrCodeFiles) > 0): ?>
            <?php $testFile = $qrCodeFiles[0]; ?>
            <div class="test-grid">
                <div class="qr-item">
                    <h3>URL Relativa</h3>
                    <img src="<?php echo $testFile['url']; ?>" alt="Teste URL Relativa" class="qr-img">
                    <p><code><?php echo htmlspecialchars($testFile['url']); ?></code></p>
                </div>
                <div class="qr-item">
                    <h3>URL Absoluta</h3>
                    <img src="<?php echo $testFile['absolute_url']; ?>" alt="Teste URL Absoluta" class="qr-img">
                    <p><code><?php echo htmlspecialchars($testFile['absolute_url']); ?></code></p>
                </div>
            </div>
        <?php else: ?>
            <p>Nenhum arquivo disponível para teste.</p>
        <?php endif; ?>
    </div>
</body>
</html> 