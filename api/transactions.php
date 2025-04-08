<?php
require_once __DIR__ . '/db_config.php'''';
require_once __DIR__ . '/models/Transaction.php';
require_once __DIR__ . '/middleware/auth.php';

// Configurar cabeçalhos para JSON e CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Registrar logs para debug
error_log("API transactions.php iniciada");

// Se for uma requisição OPTIONS (preflight), apenas retornar OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Tratamento de erro para lidar com exceções
try {
    // Verificar se o usuário está autenticado
    $userId = checkAuth();
    if (!$userId) {
        error_log("Autenticação falhou - token inválido ou expirado");
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Não autorizado"]);
        exit;
    }

    error_log("Usuário autenticado: ID $userId");

    // Processar requisição GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $transaction = new Transaction();

        // Verificar se está buscando uma transação específica
        if (isset($_GET['id'])) {
            $transactionId = $_GET['id'];
            error_log("Buscando transação específica: ID $transactionId");

            $transactionData = $transaction->getById($transactionId);

            if ($transactionData && $transactionData['userId'] == $userId) {
                echo json_encode([
                    "success" => true,
                    "transaction" => $transactionData
                ]);
            } else {
                http_response_code(404);
                echo json_encode(["success" => false, "error" => "Transação não encontrada"]);
            }
            exit;
        }

        // Obter parâmetros de paginação
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $offset = ($page - 1) * $limit;

        error_log("Buscando transações para o usuário $userId, página: $page, limite: $limit");

        // Obter o total de transações para calcular a paginação
        $totalItems = $transaction->countByUserId($userId);
        $totalPages = ceil($totalItems / $limit);

        // Obter as transações da página atual
        $transactions = $transaction->getByUserIdPaginated($userId, $limit, $offset);
        error_log("Transações encontradas: " . count($transactions));

        echo json_encode([
            "success" => true,
            "transactions" => $transactions,
            "pagination" => [
                "currentPage" => $page,
                "totalPages" => $totalPages,
                "totalItems" => $totalItems,
                "pageSize" => $limit
            ]
        ]);
        exit;
    }

    // Método não suportado
    error_log("Método não suportado: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Método não permitido"]);
} catch (Exception $e) {
    // Registrar erro e retornar resposta amigável
    error_log("Erro na API de transações: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Ocorreu um erro no servidor",
        "dev_message" => $e->getMessage()
    ]);
}
