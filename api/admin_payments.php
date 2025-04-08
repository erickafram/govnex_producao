<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Incluir o arquivo de configuração do banco de dados
require_once __DIR__ . '/config/database.php';

// Criar conexão com o banco de dados
$database = new Database();
$conn = $database->getConnection();

// Verificar autenticação
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// MODO DE DESENVOLVIMENTO - DESATIVAR EM PRODUÇÃO
// Aceitar qualquer token ou nenhum token para facilitar o desenvolvimento
$user = [
    'id' => 1,
    'nivel_acesso' => 'administrador'
];

// Apenas para log de depuração
if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    file_put_contents('auth_log.txt', "Token recebido em admin_payments.php: $token\n", FILE_APPEND);
} else {
    file_put_contents('auth_log.txt', "Nenhum token recebido em admin_payments.php, usando modo de desenvolvimento\n", FILE_APPEND);
}

// Parâmetros de paginação
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// Buscar pagamentos com paginação
try {
    // Total de pagamentos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM pagamentos");
    $totalPayments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Buscar pagamentos
    $payments = [];
    $stmt = $conn->prepare("
        SELECT p.id, p.usuario_id, u.nome as usuario_nome, p.valor, p.status, 
               p.codigo_transacao, p.data_criacao, p.data_atualizacao 
        FROM pagamentos p
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.data_criacao DESC 
        LIMIT :offset, :limit
    ");
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $payments[] = [
            'id' => $row['id'],
            'userId' => $row['usuario_id'],
            'userName' => $row['usuario_nome'],
            'amount' => floatval($row['valor']),
            'status' => $row['status'],
            'transactionCode' => $row['codigo_transacao'],
            'createdAt' => $row['data_criacao'],
            'updatedAt' => $row['data_atualizacao']
        ];
    }
    
    // Calcular total de páginas
    $totalPages = ceil($totalPayments / $limit);
    
    // Retornar os dados
    echo json_encode([
        'success' => true,
        'payments' => $payments,
        'pagination' => [
            'total' => intval($totalPayments),
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar pagamentos: ' . $e->getMessage()]);
}

// Não é necessário fechar a conexão PDO, ela será fechada automaticamente quando o script terminar
