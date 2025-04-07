<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Incluir o arquivo de configuração do banco de dados
require_once __DIR__ . '/config/database.php';

// Criar conexão com o banco de dados
$database = new Database();
$conn = $database->getConnection();

// Verificar autenticação (simplificado para desenvolvimento)
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Apenas para log de depuração
if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    file_put_contents('auth_log.txt', "Token recebido em get_users_list.php: $token\n", FILE_APPEND);
}

try {
    // Buscar lista de usuários para o dropdown
    $stmt = $conn->prepare("
        SELECT 
            id, 
            nome as name,
            email
        FROM usuarios
        ORDER BY nome ASC
    ");
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar usuários: ' . $e->getMessage()]);
}
?>
