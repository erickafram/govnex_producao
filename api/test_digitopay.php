<?php
// Configurações de cabeçalho para exibir erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir o arquivo de log
$logFile = __DIR__ . '/../logs/digitopay_test.log';

// Função para registrar logs
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    echo "[$timestamp] $message<br>";
}

// Configurações da API Digitopay
$clientUri = 'https://api.digitopayoficial.com.br/';
$clientId = '41b9547d-1053-47ee-8b57-322ca8fd67b1';
$clientSecret = '1697c51a-7b58-4370-b5dd-f54183169523';

logMessage("Iniciando teste de conexão com a Digitopay");
logMessage("URL: " . $clientUri);
logMessage("ClientID: " . substr($clientId, 0, 8) . "...");

// Testar obtenção de token
logMessage("Tentando obter token de autenticação...");

$ch = curl_init($clientUri . 'api/token/api');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode([
        'clientId' => $clientId,
        'secret' => $clientSecret
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false,  // Desativar verificação SSL para testes
    CURLOPT_TIMEOUT => 30,
    CURLOPT_VERBOSE => true
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

logMessage("HTTP Status: " . ($info['http_code'] ?? 'N/A'));

if ($error) {
    logMessage("ERRO CURL: " . $error);
    die("Falha na autenticação com a Digitopay: " . $error);
}

logMessage("Resposta recebida: " . substr($response, 0, 500));

$tokenData = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    logMessage("Erro ao decodificar JSON: " . json_last_error_msg());
    die("Resposta inválida da Digitopay");
}

if (!isset($tokenData['accessToken'])) {
    logMessage("Token não encontrado na resposta: " . json_encode($tokenData));
    die("Token não encontrado na resposta da Digitopay");
}

$token = $tokenData['accessToken'];
logMessage("Token obtido com sucesso: " . substr($token, 0, 10) . "...");

// Testar criação de pagamento
logMessage("Tentando criar um pagamento de teste...");

$paymentData = [
    "dueDate" => date('Y-m-d\TH:i:s', strtotime('+1 day')),
    "paymentOptions" => ["PIX"],
    "person" => [
        "cpf" => "12345678909",
        "name" => "Usuário Teste"
    ],
    "value" => 20.00,
    "callbackUrl" => "http://localhost/react/govnex/pix-credit-nexus/api/webhook_payments.php"
];

$ch = curl_init($clientUri . 'api/deposit');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode($paymentData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_VERBOSE => true
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

logMessage("HTTP Status: " . ($info['http_code'] ?? 'N/A'));

if ($error) {
    logMessage("ERRO CURL: " . $error);
    die("Falha na criação do pagamento: " . $error);
}

logMessage("Resposta recebida: " . substr($response, 0, 500));

$paymentResponse = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    logMessage("Erro ao decodificar JSON: " . json_last_error_msg());
    die("Resposta inválida da Digitopay");
}

if (isset($paymentResponse['error']) || isset($paymentResponse['errors'])) {
    $errorMsg = isset($paymentResponse['error']) ? $paymentResponse['error'] : json_encode($paymentResponse['errors']);
    logMessage("Erro retornado pela API: " . $errorMsg);
    die("Erro na API Digitopay: " . $errorMsg);
}

logMessage("Pagamento criado com sucesso!");
logMessage("ID da transação: " . ($paymentResponse['id'] ?? 'N/A'));
logMessage("Código PIX: " . ($paymentResponse['pixCopiaECola'] ?? 'N/A'));

echo "<h2>Teste concluído com sucesso!</h2>";
echo "<p>Verifique o arquivo de log para mais detalhes: $logFile</p>";
