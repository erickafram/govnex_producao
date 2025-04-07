<?php
require_once __DIR__ . '/controllers/ConsultaController.php';

// Criar instância do controller
$consultaController = new ConsultaController();

// Processar a requisição com base no método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Verificar se é uma requisição para estatísticas
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$isEstatisticas = strpos($path, '/api/consultas-estatisticas.php') !== false;

if ($isEstatisticas) {
    $consultaController->getEstatisticasConsultas();
    exit;
}

switch ($method) {
    case 'GET':
        // Obter consultas disponíveis e histórico
        $consultaController->getConsultasDisponiveis();
        break;
    case 'POST':
        // Registrar nova consulta
        $consultaController->registrarConsulta();
        break;
    default:
        // Método não permitido
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
}
