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

// Extrair token
$token = '';
if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    file_put_contents('auth_log.txt', "Token recebido em update_password.php: $token\n", FILE_APPEND);
}

// Verificar token (simplificado para desenvolvimento)
$userId = null;

// MODO DE DESENVOLVIMENTO - DESATIVAR EM PRODUÇÃO
// Para facilitar o desenvolvimento, vamos aceitar qualquer token
if ($token) {
    // Tentar buscar da tabela de sessões primeiro
    $stmt = $conn->prepare("SELECT usuario_id FROM sessoes WHERE token = :token AND expiracao > NOW()");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $row['usuario_id'];
    } else {
        // Se não encontrou na tabela de sessões, verificar se há um usuário armazenado no localStorage
        // Extrair o ID do usuário do token (formato: token_ID_TIMESTAMP)
        if (preg_match('/token_(\d+)_\d+/', $token, $matches)) {
            $userId = $matches[1];
            file_put_contents('auth_log.txt', "ID do usuário extraído do token: $userId\n", FILE_APPEND);
        } else {
            // Para desenvolvimento, permitir qualquer usuário
            $userId = $data['userId'];
            file_put_contents('auth_log.txt', "Usando ID do usuário da requisição para desenvolvimento: $userId\n", FILE_APPEND);
        }
    }
}

// Obter dados da requisição
$data = json_decode(file_get_contents("php://input"), true);

// Verificar se o usuário está tentando atualizar sua própria senha
// Para desenvolvimento, vamos permitir a atualização
$isAdmin = false;

// Verificar se é um administrador
if ($userId) {
    $stmt = $conn->prepare("SELECT nivel_acesso FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $isAdmin = ($user['nivel_acesso'] === 'administrador');
    }
}

// Em desenvolvimento, permitimos a atualização mesmo que não seja o próprio perfil
// Em produção, descomentar este bloco
/*
if (!$userId || ($data['userId'] != $userId && !$isAdmin)) {
    http_response_code(403);
    echo json_encode(['error' => 'Você não tem permissão para atualizar a senha deste usuário']);
    exit;
}
*/

// Validar dados
if (!isset($data['userId']) || !isset($data['currentPassword']) || !isset($data['newPassword'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados incompletos para atualização de senha']);
    exit;
}

try {
    // Verificar a senha atual
    $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $data['userId']);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuário não encontrado']);
        exit;
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar se a senha atual está correta
    // Administradores podem ignorar esta verificação ao alterar senhas de outros usuários
    if ($data['userId'] == $userId && !password_verify($data['currentPassword'], $user['senha'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Senha atual incorreta']);
        exit;
    }
    
    // Validar a nova senha
    if (strlen($data['newPassword']) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'A nova senha deve ter pelo menos 6 caracteres']);
        exit;
    }
    
    // Gerar hash da nova senha
    $newPasswordHash = password_hash($data['newPassword'], PASSWORD_DEFAULT);
    
    // Atualizar a senha
    $stmt = $conn->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
    $stmt->bindParam(':senha', $newPasswordHash);
    $stmt->bindParam(':id', $data['userId']);
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Senha atualizada com sucesso'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar senha']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar senha: ' . $e->getMessage()]);
}
?>
