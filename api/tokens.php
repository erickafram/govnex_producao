<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Se for uma requisição OPTIONS (preflight), apenas retornar OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obter conexão com o banco de dados
$db = getDbConnection();

try {
    // Verificar se a tabela existe
    $checkTable = $db->query("SHOW TABLES LIKE 'api_tokens'");
    if ($checkTable->rowCount() === 0) {
        // Criar a tabela se não existir
        $sql = "
            CREATE TABLE IF NOT EXISTS api_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                token VARCHAR(64) NOT NULL,
                description VARCHAR(255) NULL,
                user_id INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                is_active BOOLEAN DEFAULT TRUE,
                UNIQUE KEY (token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $db->exec($sql);
    }

    // GET: Listar tokens
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $tokens = [];
        $stmt = $db->query("
            SELECT id, token, description, user_id, created_at, expires_at, is_active 
            FROM api_tokens 
            ORDER BY created_at DESC
        ");
        
        if ($stmt !== false) {
            $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Converter IDs para string e is_active para boolean
            foreach ($tokens as &$token) {
                $token['id'] = (string)$token['id'];
                // Garantir que is_active seja um booleano real (1/0 => true/false)
                $token['is_active'] = $token['is_active'] == 1;
                $token['user_id'] = $token['user_id'] ? (string)$token['user_id'] : null;
            }
        }
        
        echo json_encode(["success" => true, "tokens" => $tokens]);
        exit;
    }
    
    // POST: Gerar novo token
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ler dados JSON do corpo da requisição
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Extrair dados
        $description = isset($data['description']) ? $data['description'] : null;
        $userId = isset($data['userId']) && !empty($data['userId']) ? $data['userId'] : null;
        $expiresAt = isset($data['expiresAt']) && !empty($data['expiresAt']) ? $data['expiresAt'] : null;
        
        // Gerar token único
        $token = bin2hex(random_bytes(32));
        
        // Inserir no banco
        $sql = "
            INSERT INTO api_tokens (token, description, user_id, expires_at, is_active) 
            VALUES (:token, :description, :user_id, :expires_at, TRUE)
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':expires_at', $expiresAt);
        $stmt->execute();
        
        // Buscar token inserido
        $id = $db->lastInsertId();
        $stmt = $db->prepare("SELECT id, token, description, user_id, created_at, expires_at, is_active FROM api_tokens WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tokenData) {
            $tokenData['id'] = (string)$tokenData['id'];
            // Garantir que is_active seja um booleano real
            $tokenData['is_active'] = $tokenData['is_active'] == 1;
            $tokenData['user_id'] = $tokenData['user_id'] ? (string)$tokenData['user_id'] : null;
        }
        
        echo json_encode(["success" => true, "token" => $tokenData]);
        exit;
    }
    
    // PUT: Atualizar status do token
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Verificar ID
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "ID do token não informado"]);
            exit;
        }
        
        // Ler dados JSON
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Verificar dados
        if (!isset($data['isActive'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Status do token não informado"]);
            exit;
        }
        
        $isActive = (bool)$data['isActive'];
        
        // Atualizar status
        $stmt = $db->prepare("UPDATE api_tokens SET is_active = :is_active WHERE id = :id");
        $stmt->bindValue(':is_active', $isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => $isActive ? "Token ativado" : "Token desativado"]);
        } else {
            http_response_code(404);
            echo json_encode(["success" => false, "error" => "Token não encontrado"]);
        }
        exit;
    }
    
    // Método não suportado
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Método não permitido"]);
    
} catch (PDOException $e) {
    error_log("Erro na API de tokens: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Erro ao processar a solicitação"]);
} 