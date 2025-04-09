<?php
// Configuração de cabeçalhos
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Verificar se é uma requisição OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir configuração do banco de dados
require_once __DIR__ . '/config/database.php';

// Função para gerar token seguro
function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

// Verificar autenticação (apenas admin pode gerenciar tokens)
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = '';

if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Autenticação necessária']);
    exit;
}

try {
    // Criar conexão
    $database = new Database();
    $conn = $database->getConnection();
    
    // Verificar se é admin
    $stmt = $conn->prepare("
        SELECT u.id, u.nivel_acesso 
        FROM usuarios u
        JOIN sessoes s ON u.id = s.usuario_id
        WHERE s.token = :token AND s.expiracao > NOW()
    ");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        http_response_code(401);
        echo json_encode(['error' => 'Token expirado ou inválido']);
        exit;
    }
    
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userData['nivel_acesso'] !== 'administrador') {
        http_response_code(403);
        echo json_encode(['error' => 'Apenas administradores podem gerenciar tokens de API']);
        exit;
    }
    
    $adminId = $userData['id'];
    
    // Processar requisição com base no método
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // GET - Listar tokens
    if ($requestMethod === 'GET' && (!$action || $action === 'list')) {
        $stmt = $conn->prepare("
            SELECT t.id, t.token, t.description, t.is_active, 
                   t.created_at, t.expires_at, t.last_used,
                   u.id as user_id, u.nome as user_name, u.email as user_email,
                   u.dominio as user_domain
            FROM api_tokens t
            LEFT JOIN usuarios u ON t.user_id = u.id
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
        
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'tokens' => $tokens
        ]);
        exit;
    }
    
    // POST - Criar novo token
    if ($requestMethod === 'POST' && $action === 'create') {
        // Processar dados do formulário
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['user_id']) || !isset($data['description'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados incompletos']);
            exit;
        }
        
        $userId = $data['user_id'];
        $description = $data['description'];
        $newToken = generateToken();
        $expiresAt = null;
        
        // Verificar se tem data de expiração
        if (isset($data['expires_at']) && !empty($data['expires_at'])) {
            $expiresAt = $data['expires_at'];
        }
        
        // Verificar se o usuário existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Usuário não encontrado']);
            exit;
        }
        
        // Inserir novo token
        if ($expiresAt) {
            $stmt = $conn->prepare("
                INSERT INTO api_tokens (token, user_id, description, is_active, created_at, expires_at)
                VALUES (:token, :user_id, :description, TRUE, NOW(), :expires_at)
            ");
            $stmt->bindParam(':token', $newToken);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':expires_at', $expiresAt);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO api_tokens (token, user_id, description, is_active, created_at)
                VALUES (:token, :user_id, :description, TRUE, NOW())
            ");
            $stmt->bindParam(':token', $newToken);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':description', $description);
        }
        
        if ($stmt->execute()) {
            $tokenId = $conn->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Token criado com sucesso',
                'token_id' => $tokenId,
                'token' => $newToken
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar token']);
        }
        exit;
    }
    
    // POST - Ativar/Desativar token
    if ($requestMethod === 'POST' && $action === 'toggle') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['token_id']) || !isset($data['active'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados incompletos']);
            exit;
        }
        
        $tokenId = $data['token_id'];
        $isActive = $data['active'] ? TRUE : FALSE;
        
        $stmt = $conn->prepare("
            UPDATE api_tokens 
            SET is_active = :is_active
            WHERE id = :id
        ");
        $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $tokenId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => $isActive ? 'Token ativado com sucesso' : 'Token desativado com sucesso'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar token']);
        }
        exit;
    }
    
    // POST - Revogar token (excluir)
    if ($requestMethod === 'POST' && $action === 'revoke') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['token_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID do token não fornecido']);
            exit;
        }
        
        $tokenId = $data['token_id'];
        
        $stmt = $conn->prepare("DELETE FROM api_tokens WHERE id = :id");
        $stmt->bindParam(':id', $tokenId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Token revogado com sucesso'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao revogar token']);
        }
        exit;
    }
    
    // Se chegou aqui, ação desconhecida
    http_response_code(400);
    echo json_encode(['error' => 'Ação desconhecida']);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao processar requisição: ' . $e->getMessage()]);
} 