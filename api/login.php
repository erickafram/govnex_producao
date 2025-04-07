<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/UserController.php';

// Habilitar CORS para permitir requisições de diferentes origens
enableCors();

// Log para depuração
$logFile = __DIR__ . '/auth_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login request received\n", FILE_APPEND);

// Criar instância do controller
$userController = new UserController();

// Processar a requisição com base no método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Autenticar usuário
        $userController->login();
        break;
    default:
        // Método não permitido
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
}
