<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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
    file_put_contents('auth_log.txt', "Token recebido em update_user.php: $token\n", FILE_APPEND);
} else {
    file_put_contents('auth_log.txt', "Nenhum token recebido em update_user.php, usando modo de desenvolvimento\n", FILE_APPEND);
}

// Obter dados da requisição
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['userId']) || (!isset($data['domain']) && !isset($data['balance']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados incompletos para atualização do usuário']);
    exit;
}

try {
    // Preparar a query de atualização
    $updateFields = [];
    $params = [];
    
    if (isset($data['domain'])) {
        $updateFields[] = "dominio = :domain";
        $params[':domain'] = $data['domain'] === '' ? null : $data['domain'];
    }
    
    if (isset($data['balance'])) {
        $updateFields[] = "credito = :balance";
        $params[':balance'] = floatval($data['balance']);
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nenhum campo para atualizar']);
        exit;
    }
    
    $query = "UPDATE usuarios SET " . implode(", ", $updateFields) . " WHERE id = :userId";
    $params[':userId'] = $data['userId'];
    
    // Executar a query
    $stmt = $conn->prepare($query);
    $result = $stmt->execute($params);
    
    if ($result) {
        // Buscar os dados atualizados do usuário
        $stmt = $conn->prepare("
            SELECT 
                id, 
                nome as name, 
                email, 
                COALESCE(cpf, cnpj) as document, 
                telefone as phone, 
                dominio as domain, 
                credito as balance, 
                nivel_acesso as accessLevel,
                (nivel_acesso = 'administrador') as isAdmin, 
                data_cadastro as createdAt
            FROM usuarios
            WHERE id = :userId
        ");
        $stmt->bindParam(':userId', $data['userId']);
        $stmt->execute();
        
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($updatedUser) {
            // Converter isAdmin para booleano
            $updatedUser['isAdmin'] = (bool)$updatedUser['isAdmin'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'user' => $updatedUser
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Usuário atualizado, mas não foi possível recuperar os dados atualizados'
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar usuário']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar usuário: ' . $e->getMessage()]);
}
