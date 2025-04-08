<?php
require_once __DIR__ . '/controllers/ConsultaController.php';

// Criar instância do controller
$consultaController = new ConsultaController();

// Processar a requisição com base no método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Registrar consulta de CNPJ
        $consultaController->registrarConsulta();
        break;
    case 'GET':
        // Listar histórico de consultas
        if (isset($_GET['dominio'])) {
            $consultaController->listarConsultas();
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetro domínio é obrigatório']);
        }
        break;
    default:
        // Método não permitido
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
}
