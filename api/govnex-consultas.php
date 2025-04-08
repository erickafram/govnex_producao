<?php
require_once __DIR__ . '/controllers/ConsultaController.php';
require_once __DIR__ . '/db_config.php'''';

// Configurações de CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Verificar se é uma requisição OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Apenas permitir métodos GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Verificar token de autenticação
if (!isset($_GET['token'])) {
    http_response_code(401);
    echo json_encode(["error" => "Token de autenticação necessário"]);
    exit;
}

// Validar o token (exemplo simples - em produção usar método mais seguro)
$token = $_GET['token'];
$validTokens = ['seu_token_de_acesso']; // Lista de tokens válidos (substitua por verificação em banco)

if (!in_array($token, $validTokens)) {
    http_response_code(403);
    echo json_encode(["error" => "Token inválido ou expirado"]);
    exit;
}

// Obter domínio da query string
$dominio = isset($_GET['dominio']) ? $_GET['dominio'] : null;

if (!$dominio) {
    http_response_code(400);
    echo json_encode(['error' => 'Domínio não informado']);
    exit;
}

// Verificar se deve incluir as consultas
$incluirConsultas = isset($_GET['incluirConsultas']) && $_GET['incluirConsultas'] === 'true';

// Criar instância do controller e executar
try {
    $consultaController = new ConsultaController();
    
    // Obter estatísticas
    $totalConsultas = $consultaController->consultaModel->getTotalConsultas($dominio);
    $totalGasto = $consultaController->consultaModel->getTotalGasto($dominio);
    
    $response = [
        'success' => true,
        'totalConsultas' => $totalConsultas,
        'totalGasto' => $totalGasto,
        'dominio' => $dominio
    ];
    
    // Adicionar consultas se solicitado
    if ($incluirConsultas) {
        $consultas = $consultaController->consultaModel->getByDominio($dominio, 0); // 0 para buscar todas
        $response['consultas'] = $consultas;
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro ao processar a solicitação', 
        'message' => $e->getMessage()
    ]);
} 
