<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Configurar relatório de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log de debug
function logDebug($message, $data = null) {
    $log = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $log .= " - " . (is_array($data) || is_object($data) ? json_encode($data) : $data);
    }
    $log .= "\n";
    file_put_contents(__DIR__ . '/debug_profile.log', $log, FILE_APPEND);
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

// Obter dados da requisição
$data = json_decode(file_get_contents("php://input"), true);
logDebug("Dados recebidos", $data);

// Linha adicionada para evitar erro quando $data não está definido na verificação do token
if (!isset($data['userId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do usuário não informado']);
    exit;
}

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
    
    // Verificar se o usuário é administrador
    $stmt = $conn->prepare("SELECT nivel_acesso FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $data['userId']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $isAdmin = ($user['nivel_acesso'] === 'administrador');
    
    // Determinar se o documento é CPF ou CNPJ
    $cpf = null;
    $cnpj = null;
    
    // Remover caracteres não numéricos
    $documentoNumerico = preg_replace('/[^0-9]/', '', $data['document']);
    
    if (strlen($documentoNumerico) <= 11) {
        $cpf = $data['document'];
    } else {
        $cnpj = $data['document'];
    }
    
    // Obter os valores dos parâmetros
    $name = $data['name'];
    $email = $data['email'];
    $phone = isset($data['phone']) ? $data['phone'] : null;
    $userId = $data['userId'];
    $domain = isset($data['domain']) ? $data['domain'] : null;
    
    // ABORDAGEM SIMPLIFICADA - USAR ATUALIZAÇÕES SEPARADAS PARA CADA CAMPO
    
    // 1. Atualizar campos básicos (nome, email, cpf, cnpj)
    $basicSql = "UPDATE usuarios SET 
                nome = :name, 
                email = :email, 
                cpf = :cpf, 
                cnpj = :cnpj
                WHERE id = :id";
    
    $basicStmt = $conn->prepare($basicSql);
    $basicStmt->bindParam(':name', $name);
    $basicStmt->bindParam(':email', $email);
    $basicStmt->bindParam(':cpf', $cpf);
    $basicStmt->bindParam(':cnpj', $cnpj);
    $basicStmt->bindParam(':id', $userId);
    
    logDebug("SQL básico", $basicSql);
    logDebug("Parâmetros básicos", [
        'name' => $name,
        'email' => $email,
        'cpf' => $cpf,
        'cnpj' => $cnpj,
        'id' => $userId
    ]);
    
    $basicResult = $basicStmt->execute();
    
    if (!$basicResult) {
        throw new PDOException("Erro ao atualizar campos básicos");
    }
    
    // 2. Atualizar telefone
    $phoneSql = "UPDATE usuarios SET telefone = :phone WHERE id = :id";
    $phoneStmt = $conn->prepare($phoneSql);
    $phoneStmt->bindParam(':phone', $phone);
    $phoneStmt->bindParam(':id', $userId);
    
    logDebug("SQL telefone", $phoneSql);
    logDebug("Parâmetros telefone", [
        'phone' => $phone,
        'id' => $userId
    ]);
    
    $phoneResult = $phoneStmt->execute();
    
    if (!$phoneResult) {
        logDebug("Erro ao atualizar telefone");
    }
    
    // 3. Atualizar domínio apenas se não for admin ou se for admin e forneceu o domínio
    if (!$isAdmin || isset($data['domain'])) {
        $domainSql = "UPDATE usuarios SET dominio = :domain WHERE id = :id";
        $domainStmt = $conn->prepare($domainSql);
        $domainStmt->bindParam(':domain', $domain);
        $domainStmt->bindParam(':id', $userId);
        
        logDebug("SQL domínio", $domainSql);
        logDebug("Parâmetros domínio", [
            'domain' => $domain,
            'id' => $userId
        ]);
        
        $domainResult = $domainStmt->execute();
        
        if (!$domainResult) {
            logDebug("Erro ao atualizar domínio");
        }
    }
    
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
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($updatedUser) {
        // Converter isAdmin para booleano
        $updatedUser['isAdmin'] = (bool)$updatedUser['isAdmin'];
        
        logDebug("Usuário atualizado com sucesso", $updatedUser);
        
        echo json_encode([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso',
            'user' => $updatedUser
        ]);
    } else {
        logDebug("Usuário atualizado, mas não foi possível recuperar os dados");
        
        echo json_encode([
            'success' => true,
            'message' => 'Perfil atualizado, mas não foi possível recuperar os dados atualizados'
        ]);
    }
} catch (PDOException $e) {
    // Log detalhado para erros
    $errorMessage = "===== ERRO " . date('Y-m-d H:i:s') . " =====\n";
    $errorMessage .= "Código: " . $e->getCode() . "\n";
    $errorMessage .= "Mensagem: " . $e->getMessage() . "\n";
    
    // Verificar se as variáveis estão definidas antes de usá-las
    if (isset($basicSql)) {
        $errorMessage .= "SQL Básico: " . $basicSql . "\n";
    }
    if (isset($phoneSql)) {
        $errorMessage .= "SQL Telefone: " . $phoneSql . "\n";
    }
    if (isset($domainSql)) {
        $errorMessage .= "SQL Domínio: " . $domainSql . "\n";
    }
    
    if (isset($stmt) && $stmt instanceof PDOStatement) {
        $errorInfo = $stmt->errorInfo();
        $errorMessage .= "SQL Error: " . print_r($errorInfo, true) . "\n";
    }
    
    $errorMessage .= "\n";
    
    // Usar file_put_contents com verificação de erro
    @file_put_contents(__DIR__ . '/profile_errors.log', $errorMessage, FILE_APPEND);
    
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar perfil: ' . $e->getMessage()]);
}
?>
