<?php
require_once __DIR__ . '/controllers/UserController.php';

// Criar instância do controller
$userController = new UserController();

// Processar a requisição com base no método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Registrar novo usuário
        $userController->register();
        break;
    default:
        // Método não permitido
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
}
