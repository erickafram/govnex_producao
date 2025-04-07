<?php
/**
 * Proxy para redirecionar solicitações da API GovNex para nossa API local
 * Este arquivo deve ser colocado na pasta acessível pelo domínio infovisa.gurupi.to.gov.br/api/govnex
 */

// Configurações de segurança
require_once __DIR__ . '/../../conf/db_connection.php';

// Configurações de CORS
$allowedOrigins = [
    'https://govnex.site',
    'https://infovisa.gurupi.to.gov.br',
    'http://localhost:8080',
    'http://localhost:3000'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Verificar se é uma requisição OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verificar token de autenticação
if (!isset($_GET['token'])) {
    http_response_code(401);
    echo json_encode(["error" => "Token de autenticação necessário"]);
    exit;
}

$token = $_GET['token'];

// Validar token no banco de dados
try {
    $database = new Database();
    $conn = $database->getConnection();

    $stmt = $conn->prepare("SELECT * FROM api_tokens WHERE token = :token AND is_active = TRUE");
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        http_response_code(403);
        echo json_encode(["error" => "Token inválido ou expirado"]);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao validar token"]);
    exit;
}

// URL da API local (substitua pelo endereço correto da sua API interna)
$apiBaseUrl = "http://localhost:8000/api";

// Obter o endpoint solicitado
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'consultas-estatisticas.php';

// Construir a URL completa
$apiUrl = $apiBaseUrl . "/" . $endpoint;

// Transferir todos os parâmetros GET da solicitação original, exceto o token e endpoint
$queryParams = [];
foreach ($_GET as $key => $value) {
    if ($key != 'token' && $key != 'endpoint') {
        $queryParams[] = urlencode($key) . '=' . urlencode($value);
    }
}

// Adicionar parâmetros à URL
if (!empty($queryParams)) {
    $apiUrl .= '?' . implode('&', $queryParams);
}

// Registrar a solicitação de API
try {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Desconhecido';
    $requestedEndpoint = $endpoint;
    
    $stmt = $conn->prepare("
        INSERT INTO api_access_log 
        (token, ip_address, user_agent, endpoint, access_time) 
        VALUES (:token, :ip, :user_agent, :endpoint, NOW())
    ");
    
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':ip', $ipAddress);
    $stmt->bindParam(':user_agent', $userAgent);
    $stmt->bindParam(':endpoint', $requestedEndpoint);
    $stmt->execute();
} catch (PDOException $e) {
    // Apenas registrar o erro, não interromper a solicitação
    error_log("Erro ao registrar acesso à API: " . $e->getMessage());
}

// Iniciar a solicitação cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Se for POST, configurar os dados POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}

// Definir cabeçalhos apropriados
$headers = ['Content-Type: application/json'];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Executar a solicitação
$response = curl_exec($ch);

// Verificar erros
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode([
        "error" => "Erro ao acessar a API", 
        "message" => curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

// Obter o código de status HTTP da resposta
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
http_response_code($httpCode);

// Fechar a conexão cURL
curl_close($ch);

// Retornar a resposta
echo $response; 