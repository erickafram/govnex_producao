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
    file_put_contents('auth_log.txt', "Token recebido: $token\n", FILE_APPEND);
} else {
    file_put_contents('auth_log.txt', "Nenhum token recebido, usando modo de desenvolvimento\n", FILE_APPEND);
}

// Buscar estatísticas
try {
    // Total de usuários
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de crédito em todas as contas
    $stmt = $conn->query("SELECT SUM(credito) as total FROM usuarios");
    $totalBalance = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    
    // Total de pagamentos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM pagamentos");
    $totalPayments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $conn->query("SELECT SUM(valor) as total FROM pagamentos WHERE status = 'pago'");
    $totalPaymentsValue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    
    // Total de consultas
    $stmt = $conn->query("SELECT COUNT(*) as total FROM consultas_log");
    $totalConsultas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $conn->query("SELECT SUM(custo) as total FROM consultas_log");
    $totalConsultasValue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    
    // Últimos 5 usuários
    $recentUsers = [];
    $stmt = $conn->query("SELECT id, nome, email, telefone, cpf, cnpj, nivel_acesso, credito, data_cadastro FROM usuarios ORDER BY data_cadastro DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recentUsers[] = [
            'id' => $row['id'],
            'name' => $row['nome'],
            'email' => $row['email'],
            'phone' => $row['telefone'],
            'document' => $row['cpf'] ?: $row['cnpj'],
            'accessLevel' => $row['nivel_acesso'],
            'balance' => floatval($row['credito']),
            'createdAt' => $row['data_cadastro']
        ];
    }
    
    // Últimos 5 pagamentos
    $recentPayments = [];
    $stmt = $conn->query("
        SELECT p.id, p.usuario_id, u.nome as usuario_nome, p.valor, p.status, p.codigo_transacao, p.data_criacao, p.data_atualizacao 
        FROM pagamentos p
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.data_criacao DESC LIMIT 5
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recentPayments[] = [
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
    
    // Últimas 5 consultas
    $recentConsultas = [];
    $stmt = $conn->query("
        SELECT id, cnpj_consultado, dominio_origem, data_consulta, custo 
        FROM consultas_log 
        ORDER BY data_consulta DESC LIMIT 5
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recentConsultas[] = [
            'id' => $row['id'],
            'cnpj' => $row['cnpj_consultado'],
            'domain' => $row['dominio_origem'],
            'date' => $row['data_consulta'],
            'cost' => floatval($row['custo'])
        ];
    }
    
    // Retornar os dados
    echo json_encode([
        'success' => true,
        'stats' => [
            'totalUsers' => intval($totalUsers),
            'totalBalance' => floatval($totalBalance),
            'totalPayments' => intval($totalPayments),
            'totalPaymentsValue' => floatval($totalPaymentsValue),
            'totalConsultas' => intval($totalConsultas),
            'totalConsultasValue' => floatval($totalConsultasValue)
        ],
        'recentData' => [
            'users' => $recentUsers,
            'payments' => $recentPayments,
            'consultas' => $recentConsultas
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar estatísticas: ' . $e->getMessage()]);
}

// Não é necessário fechar a conexão PDO, ela será fechada automaticamente quando o script terminar
: ' . $e->getMessage()]);
}

// Não é necessário fechar a conexão PDO, ela será fechada automaticamente quando o script terminar
semana'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Retornar estatísticas
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao processar requisição: ' . $e->getMessage()]);
}

// Não é necessário fechar a conexão PDO, ela será fechada automaticamente quando o script terminar
