<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0777, true);
    chmod($logsDir, 0777);
}

// Log file for testing
$logFile = $logsDir . '/qrcode_test.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Start test
logMessage("QR Code test endpoint called");
logMessage("Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set'));

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
                'full_path' => "{$tempDir}/{$file}",
                'exists' => file_exists("{$tempDir}/{$file}"),
                'size' => filesize("{$tempDir}/{$file}"),
                'permissions' => substr(sprintf('%o', fileperms("{$tempDir}/{$file}")),-4),
            ];
        }
    }
} else {
    logMessage("Temp directory does not exist at: $tempDir");
}

// Create sample HTML to test QR codes
$html = "<html><head><title>QR Code Test</title></head><body>";
$html .= "<h1>QR Code Test</h1>";
$html .= "<p>Server name: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "</p>";
$html .= "<p>Document root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</p>";
$html .= "<p>Temp directory: {$tempDir}</p>";

if (count($qrCodeFiles) > 0) {
    $html .= "<h2>Found " . count($qrCodeFiles) . " QR code files:</h2>";
    $html .= "<div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;'>";
    
    foreach ($qrCodeFiles as $file) {
        $html .= "<div style='border: 1px solid #ccc; padding: 10px; text-align: center;'>";
        $html .= "<img src='{$file['url']}' alt='{$file['filename']}' style='max-width: 100%; height: auto;'>";
        $html .= "<p>File: {$file['filename']}</p>";
        $html .= "<p>Size: {$file['size']} bytes</p>";
        $html .= "<p>Permissions: {$file['permissions']}</p>";
        $html .= "</div>";
    }
    
    $html .= "</div>";
} else {
    $html .= "<p>No QR code files found in temp directory.</p>";
}

$html .= "</body></html>";

// Response with information about QR code files
$result = [
    'success' => true,
    'message' => 'QR code test results',
    'timestamp' => date('Y-m-d H:i:s'),
    'temp_directory' => [
        'path' => $tempDir,
        'web_path' => $webTempDir,
        'exists' => is_dir($tempDir),
        'writable' => is_writable($tempDir),
        'permissions' => is_dir($tempDir) ? substr(sprintf('%o', fileperms($tempDir)),-4) : 'N/A',
    ],
    'qr_code_files' => $qrCodeFiles,
    'html_test_page' => $html,
];

// Output as JSON
echo json_encode($result, JSON_PRETTY_PRINT); 