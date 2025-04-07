<?php
require_once __DIR__ . '/controllers/UserController.php';

// Criar instância do controller
$userController = new UserController();

// Processar a requisição com base no método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Verificar se foi solicitado um usuário específico
        if (isset($_GET['id'])) {
            $userController->getUser($_GET['id']);
        } else {
            // Listar todos os usuários
            $userController->listAll();
        }
        break;
    default:
        // Método não permitido
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
}
