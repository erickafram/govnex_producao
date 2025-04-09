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
       p.data_atualizacao
        FROM pagamentos p
        JOIN usuarios u ON p.usuario_id = u.id
    ";
    
    // Adicionar cláusulas WHERE
    $whereClause = "";
    $params = [];
    
    if (!empty($search)) {
        $whereClause .= " WHERE (u.nome LIKE :search OR u.email LIKE :search OR p.codigo_transacao LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($status)) {
        if (empty($whereClause)) {
            $whereClause .= " WHERE p.status = :status";
        } else {
            $whereClause .= " AND p.status = :status";
        }
        $params[':status'] = $status;
    }
    
    // Adicionar WHERE às queries
    $countQuery .= $whereClause;
    $query .= $whereClause;
    
    // Adicionar ordenação e paginação
    $query .= " ORDER BY p.$orderBy $orderDir LIMIT :offset, :perPage";
    
    // Executar query de contagem
    $stmt = $conn->prepare($countQuery);
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }
    $stmt->execute();
    $totalPayments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Executar query principal
    $stmt = $conn->prepare($query);
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    
    $payments = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $payments[] = [
            'id' => $row['id'],
            'userId' => $row['usuario_id'],
            'userName' => $row['usuario_nome'],
            'userEmail' => $row['usuario_email'],
            'amount' => floatval($row['valor']),
            'status' => $row['status'],
            'paymentType' => $row['tipo_pagamento'],
            'description' => $row['descricao'],
            'transactionCode' => $row['codigo_transacao'],
            'boletoUrl' => $row['url_boleto'],
            'qrcodeUrl' => $row['url_qrcode'],
            'createdAt' => $row['data_criacao'],
            'updatedAt' => $row['data_atualizacao']
        ];
    }
    
    // Calcular total de páginas
    $totalPages = ceil($totalPayments / $perPage);
    
    // Estatísticas de pagamentos
    $stats = [];
    
    // Total por status
    $stmt = $conn->query("SELECT status, COUNT(*) as total, SUM(valor) as valor_total FROM pagamentos GROUP BY status");
    $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Total geral
    $stmt = $conn->query("SELECT COUNT(*) as total, SUM(valor) as valor_total FROM pagamentos");
    $totalStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total'] = [
        'count' => intval($totalStats['total']),
        'amount' => floatval($totalStats['valor_total'] ?: 0)
    ];
    
    // Retornar resposta
    echo json_encode([
        'success' => true,
        'payments' => $payments,
        'stats' => $stats,
        'pagination' => [
            'total' => $totalPayments,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $totalPages,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $totalPayments)
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao processar requisição: ' . $e->getMessage()]);
}

// Não é necessário fechar a conexão PDO, ela será fechada automaticamente quando o script terminar
