<?php
// Configurações de cabeçalho para CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir arquivos necessários
require_once __DIR__ . '/config/database.php';

// Arquivo de log para webhook
$logFile = __DIR__ . '/../logs/webhook_payments.log';

// Verificar se o diretório de logs existe
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Função para registrar logs
function logWebhook($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Conectar ao banco de dados
$database = new Database();
$db = $database->getConnection();

// Função para atualizar o status de um pagamento
function updatePaymentStatus($db, $transactionId, $newStatus) {
    logWebhook("Tentando atualizar pagamento: $transactionId -> $newStatus");
    
    // Atualizar status do pagamento
    $query = "UPDATE pagamentos SET status = :status, data_atualizacao = NOW() WHERE codigo_transacao = :codigo_transacao";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":status", $newStatus);
    $stmt->bindParam(":codigo_transacao", $transactionId);
    
    try {
        $stmt->execute();
        
        // Verificar se alguma linha foi afetada
        if ($stmt->rowCount() > 0) {
            logWebhook("Pagamento atualizado com sucesso: $transactionId -> $newStatus");
            
            // Se o pagamento foi confirmado, adicionar créditos ao usuário
            if ($newStatus === 'pago') {
                // Obter informações do pagamento
                $query = "SELECT usuario_id, valor FROM pagamentos WHERE codigo_transacao = :codigo_transacao";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":codigo_transacao", $transactionId);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                    $userId = $payment['usuario_id'];
                    $amount = $payment['valor'];
                    
                    // Adicionar créditos ao usuário
                    $query = "UPDATE usuarios SET credito = credito + :amount WHERE id = :user_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(":amount", $amount);
                    $stmt->bindParam(":user_id", $userId);
                    
                    try {
                        $stmt->execute();
                        logWebhook("Créditos adicionados ao usuário $userId: R$ $amount");
                        
                        // Registrar transação
                        $query = "INSERT INTO consultas_log (cnpj_consultado, dominio_origem, data_consulta, custo) 
                                  VALUES ('RECARGA-PIX', (SELECT dominio FROM usuarios WHERE id = :user_id), NOW(), :amount)";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(":user_id", $userId);
                        $stmt->bindParam(":amount", $amount);
                        $stmt->execute();
                        
                        return [
                            "success" => true,
                            "message" => "Status atualizado com sucesso e créditos adicionados",
                            "userId" => $userId,
                            "amount" => $amount
                        ];
                    } catch (PDOException $e) {
                        logWebhook("Erro ao adicionar créditos: " . $e->getMessage());
                        return [
                            "success" => false,
                            "message" => "Erro ao adicionar créditos: " . $e->getMessage()
                        ];
                    }
                } else {
                    logWebhook("Pagamento encontrado, mas não foi possível obter detalhes do usuário e valor");
                    return [
                        "success" => true,
                        "message" => "Status atualizado, mas não foi possível adicionar créditos"
                    ];
                }
            } else {
                return [
                    "success" => true,
                    "message" => "Status atualizado com sucesso"
                ];
            }
        } else {
            logWebhook("Transação não encontrada: $transactionId");
            return [
                "success" => false,
                "message" => "Transação não encontrada"
            ];
        }
    } catch (PDOException $e) {
        logWebhook("Erro ao atualizar pagamento: " . $e->getMessage());
        return [
            "success" => false,
            "message" => "Erro ao atualizar pagamento: " . $e->getMessage()
        ];
    }
}

// Verificar se é uma solicitação para processar manualmente uma transação
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['process_transaction'])) {
    $transactionId = $_GET['process_transaction'];
    $status = isset($_GET['status']) ? $_GET['status'] : 'pago';
    
    logWebhook("Solicitação manual para processar transação: $transactionId com status $status");
    
    // Validar o status
    $validStatuses = ['pago', 'pendente', 'cancelado'];
    if (!in_array($status, $validStatuses)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Status inválido"]);
        exit;
    }
    
    // Atualizar o status do pagamento
    $result = updatePaymentStatus($db, $transactionId, $status);
    
    // Retornar resultado
    http_response_code($result['success'] ? 200 : 404);
    echo json_encode($result);
    exit;
}

// Processar webhook normal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registrar recebimento do webhook
    $input = file_get_contents("php://input");
    logWebhook("Webhook recebido: " . $input);
    
    // Obter dados da requisição
    $data = json_decode($input, true);
    
    // Validar dados recebidos
    if (!isset($data['id']) || !isset($data['status'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Dados incompletos"]);
        logWebhook("Dados incompletos no webhook: " . json_encode($data));
        exit;
    }
    
    // Mapeamento de status da Digitopay para nosso sistema
    $statusMapping = [
        'REALIZADO' => 'pago',
        'PENDENTE' => 'pendente',
        'CANCELADO' => 'cancelado'
    ];
    
    // Verificar se o status é válido
    if (!isset($statusMapping[$data['status']])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Status inválido"]);
        logWebhook("Status inválido no webhook: " . $data['status']);
        exit;
    }
    
    $transactionId = $data['id'];
    $newStatus = $statusMapping[$data['status']];
    
    // Atualizar o status do pagamento
    $result = updatePaymentStatus($db, $transactionId, $newStatus);
    
    // Retornar resultado
    http_response_code($result['success'] ? 200 : 404);
    echo json_encode($result);
    exit;
}

// Se não for POST nem GET com process_transaction
http_response_code(405);
echo json_encode(["success" => false, "message" => "Método não permitido"]);
logWebhook("Método não permitido: " . $_SERVER['REQUEST_METHOD']);
