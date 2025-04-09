<?php
// Configurações de cabeçalho para CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Configurar exibição de erros (apenas para desenvolvimento)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Função para registrar logs
function logError($message) {
    $logFile = __DIR__ . '/../logs/payment_api.log';
    
    // Verificar se o diretório de logs existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Gravar a mensagem no arquivo de log
    $logMessage = "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Se for uma requisição OPTIONS, retornar apenas os cabeçalhos
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Método não permitido"]);
    exit;
}

try {
    // Incluir arquivos necessários
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/models/Payment.php';
    require_once __DIR__ . '/auth/validate_token.php';

    // Obter dados da requisição
    $jsonInput = file_get_contents("php://input");
    logError("Dados recebidos: " . $jsonInput);
    
    $data = json_decode($jsonInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logError("Erro ao decodificar JSON: " . json_last_error_msg());
        http_response_code(400);
        echo json_encode(["message" => "Dados inválidos"]);
        exit;
    }
    
    logError("Dados recebidos: " . json_encode($data));

    // Validar token
    $userId = validateToken();
    
    if (!$userId) {
        logError("Token inválido ou expirado");
        http_response_code(401);
        echo json_encode(["message" => "Token inválido ou expirado"]);
        exit;
    }
    
    logError("Usuário autenticado: " . $userId);

    // Validar dados recebidos
    if (!isset($data['amount']) || !isset($data['cpf']) || !isset($data['name'])) {
        logError("Dados incompletos: " . json_encode($data));
        http_response_code(400);
        echo json_encode(["message" => "Dados incompletos. Informe valor, CPF e nome."]);
        exit;
    }

    // Validar valor mínimo
    if ($data['amount'] < 1) {
        logError("Valor abaixo do mínimo: " . $data['amount']);
        http_response_code(400);
        echo json_encode(["message" => "O valor mínimo para recarga é R$ 1,00."]);
        exit;
    }

    // Criar instância do Payment
    $payment = new Payment();
    
    logError("Iniciando criação de pagamento para usuário " . $userId . " no valor de R$ " . $data['amount']);

    // Criar pagamento
    $result = $payment->createPayment(
        $userId,
        $data['amount'],
        [
            'cpf' => $data['cpf'],
            'name' => $data['name']
        ]
    );
    
    logError("Resultado da criação de pagamento: " . json_encode($result));

    // Retornar resultado
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(500);
        echo json_encode(["message" => $result['message']]);
    }

} catch (Exception $e) {
    logError("Erro na API de pagamento: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(["message" => "Erro ao criar pagamento: " . $e->getMessage()]);
}
