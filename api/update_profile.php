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
    file_put_contents('auth_log.txt', "Token recebido em update_profile.php: $token\n", FILE_APPEND);
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

// Verificar se o usuário está tentando atualizar seu próprio perfil
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
    echo json_encode(['error' => 'Você não tem permissão para atualizar este perfil']);
    exit;
}
*/

// Validar dados
if (!isset($data['userId']) || !isset($data['name']) || !isset($data['email']) || !isset($data['document'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados incompletos para atualização do perfil']);
    exit;
}

try {
    // Verificar se o email já está em uso por outro usuário
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':id', $data['userId']);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Este e-mail já está em uso por outro usuário']);
        exit;
    }
    
    // Verificar se o documento já está em uso por outro usuário
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE (cpf = :document OR cnpj = :document) AND id != :id");
    $stmt->bindParam(':document', $data['document']);
    $stmt->bindParam(':id', $data['userId']);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Este CPF/CNPJ já está em uso por outro usuário']);
        exit;
    }
    
    // Determinar se o documento é CPF ou CNPJ
    $cpf = null;
    $cnpj = null;
    
    if (strlen(preg_replace('/[^0-9]/', '', $data['document'])) <= 11) {
        $cpf = $data['document'];
    } else {
        $cnpj = $data['document'];
    }
    
    // Preparar a query de atualização
    $sql = "UPDATE usuarios SET 
            nome = :name, 
            email = :email, 
            cpf = :cpf, 
            cnpj = :cnpj, 
            telefone = :phone";
    
    // Adicionar domínio apenas se o usuário não for administrador
    $stmt = $conn->prepare("SELECT nivel_acesso FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $data['userId']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user['nivel_acesso'] !== 'administrador' && isset($data['domain'])) {
        $sql .= ", dominio = :domain";
    }
    
    $sql .= " WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':cpf', $cpf);
    $stmt->bindParam(':cnpj', $cnpj);
    $stmt->bindParam(':phone', $data['phone']);
    
    if ($user['nivel_acesso'] !== 'administrador' && isset($data['domain'])) {
        $domain = $data['domain'] === '' ? null : $data['domain'];
        $stmt->bindParam(':domain', $domain);
    }
    
    $stmt->bindParam(':id', $data['userId']);
    
    $result = $stmt->execute();
    
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
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $data['userId']);
        $stmt->execute();
        
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($updatedUser) {
            // Converter isAdmin para booleano
            $updatedUser['isAdmin'] = (bool)$updatedUser['isAdmin'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'user' => $updatedUser
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Perfil atualizado, mas não foi possível recuperar os dados atualizados'
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar perfil']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar perfil: ' . $e->getMessage()]);
}
?>
