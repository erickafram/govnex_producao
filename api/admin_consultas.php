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
    file_put_contents('auth_log.txt', "Token recebido em admin_consultas.php: $token\n", FILE_APPEND);
} else {
    file_put_contents('auth_log.txt', "Nenhum token recebido em admin_consultas.php, usando modo de desenvolvimento\n", FILE_APPEND);
}

// Parâmetros de paginação
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// Buscar consultas com paginação
try {
    // Total de consultas
    $stmt = $conn->query("SELECT COUNT(*) as total FROM consultas_log");
    $totalConsultas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Buscar consultas
    $consultas = [];
    $stmt = $conn->prepare("
        SELECT c.id, c.cnpj_consultado, c.dominio_origem, c.data_consulta, c.custo,
               u.id as usuario_id, u.nome as usuario_nome
        FROM consultas_log c
        LEFT JOIN usuarios u ON c.dominio_origem = u.dominio
        ORDER BY c.data_consulta DESC 
        LIMIT :offset, :limit
    ");
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $consultas[] = [
            'id' => $row['id'],
            'cnpj' => $row['cnpj_consultado'],
            'domain' => $row['dominio_origem'],
            'userId' => $row['usuario_id'],
            'userName' => $row['usuario_nome'],
            'date' => $row['data_consulta'],
            'cost' => floatval($row['custo'])
        ];
    }
    
    // Calcular total de páginas
    $totalPages = ceil($totalConsultas / $limit);
    
    // Retornar os dados
    echo json_encode([
        'success' => true,
        'consultas' => $consultas,
        'pagination' => [
            'total' => intval($totalConsultas),
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar consultas: ' . $e->getMessage()]);
}

// Não é necessário fechar a conexão PDO, ela será fechada automaticamente quando o script terminar
l.data_consulta, 
            cl.custo,
            cl.usuario_id,
            u.nome as usuario_nome,
            u.email as usuario_email
        FROM consultas_log cl
        JOIN usuarios u ON cl.usuario_id = u.id
    ";
    
    // Adicionar cláusulas WHERE
    $whereClause = "";
    $params = [];
    
    if (!empty($search)) {
        $whereClause .= " WHERE (cl.cnpj_consultado LIKE :search OR u.nome LIKE :search OR u.email LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($dominio)) {
        if (empty($whereClause)) {
            $whereClause .= " WHERE cl.dominio_origem = :dominio";
        } else {
            $whereClause .= " AND cl.dominio_origem = :dominio";
        }
        $params[':dominio'] = $dominio;
    }
    
    if ($usuario > 0) {
        if (empty($whereClause)) {
            $whereClause .= " WHERE cl.usuario_id = :usuario_id";
        } else {
            $whereClause .= " AND cl.usuario_id = :usuario_id";
        }
        $params[':usuario_id'] = $usuario;
    }
    
    if (!empty($dataInicio)) {
        if (empty($whereClause)) {
            $whereClause .= " WHERE cl.data_consulta >= :data_inicio";
        } else {
            $whereClause .= " AND cl.data_consulta >= :data_inicio";
        }
        $params[':data_inicio'] = $dataInicio . ' 00:00:00';
    }
    
    if (!empty($dataFim)) {
        if (empty($whereClause)) {
            $whereClause .= " WHERE cl.data_consulta <= :data_fim";
        } else {
            $whereClause .= " AND cl.data_consulta <= :data_fim";
        }
        $params[':data_fim'] = $dataFim . ' 23:59:59';
    }
    
    // Adicionar WHERE às queries
    $countQuery .= $whereClause;
    $query .= $whereClause;
    
    // Adicionar ordenação e paginação
    $query .= " ORDER BY cl.$orderBy $orderDir LIMIT :offset, :perPage";
    
    // Executar query de contagem
    $stmt = $conn->prepare($countQuery);
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }
    $stmt->execute();
    $totalConsultas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Executar query principal
    $stmt = $conn->prepare($query);
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    
    $consultas = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Formatar CPF ou CNPJ
        $documento = $row['cnpj_consultado'];
        
        $consultas[] = [
            'id' => $row['id'],
            'documento' => $documento,
            'dominio' => $row['dominio_origem'],
            'data' => $row['data_consulta'],
            'custo' => floatval($row['custo']),
            'usuarioId' => $row['usuario_id'],
            'usuarioNome' => $row['usuario_nome'],
            'usuarioEmail' => $row['usuario_email']
        ];
    }
    
    // Calcular total de páginas
    $totalPages = ceil($totalConsultas / $perPage);
    
    // Estatísticas de consultas
    $stats = [];
    
    // Total geral
    $stmt = $conn->query("SELECT COUNT(*) as total, SUM(custo) as custo_total FROM consultas_log");
    $totalStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total'] = [
        'count' => intval($totalStats['total']),
        'cost' => floatval($totalStats['custo_total'] ?: 0)
    ];
    
    // Por dia (últimos 30 dias)
    $stmt = $conn->query("
        SELECT 
            DATE(data_consulta) as data, 
            COUNT(*) as total,
            SUM(custo) as custo_total
        FROM consultas_log 
        WHERE data_consulta > DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(data_consulta)
        ORDER BY data
    ");
    $stats['by_day'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Por domínio
    $stmt = $conn->query("
        SELECT 
            dominio_origem, 
            COUNT(*) as total,
            SUM(custo) as custo_total
        FROM consultas_log 
        GROUP BY dominio_origem
        ORDER BY total DESC
    ");
    $stats['by_domain'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Por usuário (top 10)
    $stmt = $conn->query("
        SELECT 
            cl.usuario_id,
            u.nome,
            u.email,
            COUNT(*) as total,
            SUM(cl.custo) as custo_total
        FROM consultas_log cl
        JOIN usuarios u ON cl.usuario_id = u.id
        GROUP BY cl.usuario_id
        ORDER BY total DESC
        LIMIT 10
    ");
    $stats['by_user'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retornar resposta
    echo json_encode([
        'success' => true,
        'consultas' => $consultas,
        'stats' => $stats,
        'pagination' => [
            'total' => $totalConsultas,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $totalPages,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $totalConsultas)
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
