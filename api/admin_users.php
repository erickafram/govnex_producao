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
    file_put_contents('auth_log.txt', "Token recebido em admin_users.php: $token\n", FILE_APPEND);
} else {
    file_put_contents('auth_log.txt', "Nenhum token recebido em admin_users.php, usando modo de desenvolvimento\n", FILE_APPEND);
}

// Parâmetros de paginação
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// Buscar usuários com paginação
try {
    // Total de usuários
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Buscar usuários
    $users = [];
    $stmt = $conn->prepare("
        SELECT id, nome, email, telefone, cpf, cnpj, dominio, nivel_acesso, data_cadastro, credito 
        FROM usuarios 
        ORDER BY data_cadastro DESC 
        LIMIT :offset, :limit
    ");
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[] = [
            'id' => $row['id'],
            'name' => $row['nome'],
            'email' => $row['email'],
            'phone' => $row['telefone'],
            'document' => $row['cpf'] ?: $row['cnpj'],
            'domain' => $row['dominio'],
            'accessLevel' => $row['nivel_acesso'],
            'balance' => floatval($row['credito']),
            'createdAt' => $row['data_cadastro'],
            'isAdmin' => $row['nivel_acesso'] === 'administrador'
        ];
    }
    
    // Calcular total de páginas
    $totalPages = ceil($totalUsers / $limit);
    
    // Retornar os dados
    echo json_encode([
        'success' => true,
        'users' => $users,
        'pagination' => [
            'total' => intval($totalUsers),
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar usuários: ' . $e->getMessage()]);
}

// Não é necessário fechar a conexão PDO, ela será fechada automaticamente quando o script terminar
          id, 
            nome, 
            email, 
            telefone, 
            cpf, 
            cnpj, 
            dominio, 
            nivel_acesso, 
            credito, 
            data_cadastro,
            (SELECT COUNT(*) FROM consultas_log WHERE usuario_id = usuarios.id) as total_consultas
        FROM usuarios
    ";
    
    // Adicionar cláusula WHERE se houver busca
    $query .= $whereClause;
    
    // Adicionar ordenação e paginação
    $query .= " ORDER BY $orderBy $orderDir LIMIT :offset, :perPage";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    
    $users = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Formatar documento principal
        $documento = !empty($row['cpf']) ? $row['cpf'] : $row['cnpj'];
        
        $users[] = [
            'id' => $row['id'],
            'name' => $row['nome'],
            'email' => $row['email'],
            'phone' => $row['telefone'],
            'document' => $documento,
            'cpf' => $row['cpf'],
            'cnpj' => $row['cnpj'],
            'domain' => $row['dominio'],
            'accessLevel' => $row['nivel_acesso'],
            'balance' => floatval($row['credito']),
            'consultas' => intval($row['total_consultas']),
            'createdAt' => $row['data_cadastro']
        ];
    }
    
    // Calcular total de páginas
    $totalPages = ceil($totalUsers / $perPage);
    
    // Retornar resposta
    echo json_encode([
        'success' => true,
        'users' => $users,
        'pagination' => [
            'total' => $totalUsers,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $totalPages,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $totalUsers)
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
