<?php
require_once __DIR__ . '/../config/database.php';

function validateToken() {
    // Registrar tentativa de validação
    error_log("Tentativa de validação de token iniciada");
    
    // Para ambiente de desenvolvimento, permitir acesso sem token
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
        error_log("Ambiente de desenvolvimento detectado, retornando usuário 1");
        return 1; // Retorna ID 1 para ambiente de desenvolvimento
    }
    
    // Obter todos os cabeçalhos
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    // Verificar se existe token
    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        error_log("Token não encontrado no cabeçalho Authorization");
        return false;
    }
    
    $token = $matches[1];
    error_log("Token encontrado: " . substr($token, 0, 10) . "...");
    
    // Para token de desenvolvimento específico
    if ($token === 'dev_token_user_1') {
        error_log("Token de desenvolvimento válido");
        return 1; // Retorna ID 1 para token de desenvolvimento
    }
    
    // Conectar ao banco de dados
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar se o token é válido no banco de dados
    $stmt = $db->prepare("SELECT id FROM api_tokens WHERE token = :token AND is_active = 1");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Como a tabela api_tokens não tem uma relação direta com usuários,
        // vamos retornar o usuário 1 para tokens válidos
        error_log("Token API válido, retornando usuário 1");
        return 1;
    }
    
    error_log("Token inválido ou inativo");
    return false;
}
