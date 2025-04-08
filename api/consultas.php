<?php
// Incluir configurações e funções auxiliares
require_once __DIR__ . '/config.php';

// Configurar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Responder imediatamente a requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Logging para debug
error_log("consultas.php: Método: " . $_SERVER['REQUEST_METHOD']);
error_log("consultas.php: Query params: " . json_encode($_GET));
error_log("consultas.php: Headers: " . json_encode(getallheaders()));

// Incluir controller
require_once __DIR__ . '/controllers/ConsultaController.php';

// Verificar método da requisição
$controller = new ConsultaController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Verificar se é uma solicitação para obter consultas disponíveis
    if (isset($_GET['userId'])) {
        error_log("consultas.php: Processando solicitação para obter consultas disponíveis");
        $controller->getConsultasDisponiveis($_GET['userId']);
    } else {
        error_log("consultas.php: Parâmetro userId não fornecido");
        jsonResponse(['error' => 'Parâmetro userId é obrigatório'], 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter dados do corpo da requisição
    $data = getRequestData();
    error_log("consultas.php: Dados POST: " . json_encode($data));
    
    // Verificar se é uma solicitação para registrar consulta
    if (isset($data['cnpj']) && isset($data['userId'])) {
        error_log("consultas.php: Processando solicitação para registrar consulta");
        $controller->registrarConsulta($data);
    } else {
        error_log("consultas.php: Parâmetros obrigatórios não fornecidos");
        jsonResponse(['error' => 'Parâmetros obrigatórios não fornecidos'], 400);
    }
} else {
    error_log("consultas.php: Método não suportado: " . $_SERVER['REQUEST_METHOD']);
    jsonResponse(['error' => 'Método não suportado'], 405);
}
