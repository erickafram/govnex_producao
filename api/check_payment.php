<?php
// Configurações de cabeçalho para CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir arquivos necessários
require_once __DIR__ . '/config/database.php';

// Função para registrar logs
function logCheck($message) {
    $logFile = __DIR__ . '/../logs/payment_check.log';
    
    // Verificar se o diretório de logs existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Gravar a mensagem no arquivo de log
    $logMessage = "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Verificar se é uma requisição GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
    exit;
}

// Verificar se o transaction_id foi fornecido
if (!isset($_GET['transaction_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID da transação não fornecido"]);
    exit;
}

$transactionId = $_GET['transaction_id'];
logCheck("Verificando pagamento: " . $transactionId);

// Conectar ao banco de dados
$database = new Database();
$db = $database->getConnection();

// Consultar o pagamento
$query = "SELECT p.*, u.nome as nome_usuario, u.email as email_usuario, u.credito as credito_usuario 
          FROM pagamentos p 
          LEFT JOIN usuarios u ON p.usuario_id = u.id 
          WHERE p.codigo_transacao = :codigo_transacao";
$stmt = $db->prepare($query);
$stmt->bindParam(":codigo_transacao", $transactionId);

try {
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        logCheck("Pagamento encontrado: " . json_encode($payment));
        
        // Formatar a resposta
        $response = [
            "success" => true,
            "payment" => [
                "id" => $payment['id'],
                "usuario_id" => $payment['usuario_id'],
                "nome_usuario" => $payment['nome_usuario'],
                "email_usuario" => $payment['email_usuario'],
                "credito_atual" => $payment['credito_usuario'],
                "valor" => $payment['valor'],
                "status" => $payment['status'],
                "codigo_transacao" => $payment['codigo_transacao'],
                "data_criacao" => $payment['data_criacao'],
                "data_atualizacao" => $payment['data_atualizacao']
            ]
        ];
        
        http_response_code(200);
        echo json_encode($response);
    } else {
        logCheck("Pagamento não encontrado: " . $transactionId);
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Pagamento não encontrado"]);
    }
} catch (PDOException $e) {
    logCheck("Erro ao consultar pagamento: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro ao consultar pagamento: " . $e->getMessage()]);
}
