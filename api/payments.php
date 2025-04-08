<?php
// Configurações de cabeçalho para CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Se for uma requisição OPTIONS, retornar apenas os cabeçalhos
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Função para registrar logs
function logPayments($message) {
    $logFile = __DIR__ . '/../logs/payments_api.log';
    
    // Verificar se o diretório de logs existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Gravar a mensagem no arquivo de log
    $logMessage = "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Incluir arquivos necessários
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/validate_token.php';

// Verificar se a requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Método não permitido"]);
    exit;
}

try {
    // Validar token
    $userId = validateToken();
    
    if (!$userId) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Token inválido ou expirado"]);
        exit;
    }
    
    logPayments("Usuário autenticado: " . $userId);
    
    // Parâmetros de paginação
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    
    // Conectar ao banco de dados
    $database = new Database();
    $db = $database->getConnection();
    
    // Consultar total de registros para paginação
    $countQuery = "SELECT COUNT(*) as total FROM pagamentos WHERE usuario_id = :usuario_id";
    $countStmt = $db->prepare($countQuery);
    $countStmt->bindParam(":usuario_id", $userId);
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);
    
    // Consultar transações do usuário
    $query = "SELECT id, usuario_id, valor, status, codigo_transacao, data_criacao, data_atualizacao 
              FROM pagamentos 
              WHERE usuario_id = :usuario_id 
              ORDER BY data_criacao DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":usuario_id", $userId);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $transactions = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Formatar os dados para o formato esperado pelo frontend
        $transactions[] = [
            "id" => $row['id'],
            "userId" => $row['usuario_id'],
            "type" => "deposit", // Assumindo que todos são depósitos
            "amount" => (float)$row['valor'],
            "status" => mapStatus($row['status']),
            "description" => "Recarga via PIX - R$ " . number_format((float)$row['valor'], 2, ',', '.'),
            "createdAt" => $row['data_criacao'],
            "updatedAt" => $row['data_atualizacao'],
            "transactionCode" => $row['codigo_transacao']
        ];
    }
    
    // Preparar resposta
    $response = [
        "success" => true,
        "transactions" => $transactions,
        "pagination" => [
            "totalRecords" => $totalRecords,
            "totalPages" => $totalPages,
            "currentPage" => $page,
            "pageSize" => $limit
        ]
    ];
    
    http_response_code(200);
    echo json_encode($response);
    
} catch (Exception $e) {
    logPayments("Erro ao listar pagamentos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Erro ao listar pagamentos: " . $e->getMessage()]);
}

// Função para mapear status do banco para o formato do frontend
function mapStatus($dbStatus) {
    $statusMap = [
        'pendente' => 'pending',
        'pago' => 'completed',
        'cancelado' => 'failed'
    ];
    
    return isset($statusMap[$dbStatus]) ? $statusMap[$dbStatus] : 'pending';
}
