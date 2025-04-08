<?php
require_once __DIR__ . '/config.php'; // Adicionado: incluir config.php para funções auxiliares
require_once __DIR__ . '/controllers/ConsultaController.php';

// Configurações de CORS para permitir acesso da aplicação React
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

// Criar instância do controller e chamar o método adequado
$consultaController = new ConsultaController();
$consultaController->getEstatisticasConsultas(); 
