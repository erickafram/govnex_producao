<?php
// Configurações de cabeçalho para CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Se for uma requisição OPTIONS, retornar apenas os cabeçalhos
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir arquivos necessários
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/validate_token.php';

// Verificar se a requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["message" => "Método não permitido"]);
    exit;
}

// Validar token
$userId = validateToken();
if (!$userId) {
    http_response_code(401);
    echo json_encode(["message" => "Não autorizado. Faça login novamente."]);
    exit;
}

// Verificar se o ID da transação foi fornecido
if (!isset($_GET['transaction_id']) || empty($_GET['transaction_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "ID da transação não fornecido"]);
    exit;
}

$transactionId = $_GET['transaction_id'];

// Conectar ao banco de dados
$database = new Database();
$db = $database->getConnection();

// Verificar status do pagamento no banco de dados
$query = "SELECT status FROM pagamentos WHERE codigo_transacao = :codigo_transacao AND usuario_id = :usuario_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":codigo_transacao", $transactionId);
$stmt->bindParam(":usuario_id", $userId);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        "success" => true,
        "status" => $row['status'],
        "paid" => ($row['status'] === 'pago')
    ];
    
    http_response_code(200);
    echo json_encode($response);
} else {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Transação não encontrada"
    ]);
}
