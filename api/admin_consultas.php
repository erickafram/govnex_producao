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
