<?php
// Adicionar cabeçalhos CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Responder imediatamente às solicitações OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log para depuração
$logFile = __DIR__ . '/login_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Requisição de login recebida\n", FILE_APPEND);

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Obter dados da requisição
$data = json_decode(file_get_contents('php://input'), true);
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Dados recebidos: " . json_encode($data) . "\n", FILE_APPEND);

// Validar dados
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email e senha são obrigatórios']);
    exit;
}

// Configuração do banco de dados
$host = "localhost";
$dbname = "govnex";
$username = "root";
$password = "";

try {
    // Conectar ao banco de dados
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar usuário pelo email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar se o usuário existe
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciais inválidas']);
        exit;
    }
    
    // Verificar senha
    if (!password_verify($data['password'], $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciais inválidas']);
        exit;
    }
    
    // Gerar token JWT (simplificado)
    $token = bin2hex(random_bytes(32));
    
    // Atualizar token do usuário no banco de dados
    $stmt = $conn->prepare("UPDATE users SET token = :token WHERE id = :id");
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':id', $user['id']);
    $stmt->execute();
    
    // Remover senha do resultado
    unset($user['password']);
    
    // Adicionar propriedade isAdmin
    $user['isAdmin'] = ($user['nivel_acesso'] === 'administrador');
    
    // Responder com sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso',
        'user' => $user,
        'token' => $token
    ]);
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login bem-sucedido para: " . $data['email'] . "\n", FILE_APPEND);
    
} catch (PDOException $e) {
    // Log do erro
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erro: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Responder com erro
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
