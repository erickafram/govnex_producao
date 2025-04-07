<?php
/**
 * Middleware de autenticação
 * 
 * Este arquivo contém funções para verificar a autenticação do usuário
 */

/**
 * Verifica se o usuário está autenticado
 * 
 * @return int|false ID do usuário ou false se não autenticado
 */
function checkAuth() {
    error_log("Verificando autenticação...");
    
    // Obter cabeçalhos de autorização
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    // Verificar se o cabeçalho de autorização existe e começa com 'Bearer '
    if (empty($authHeader) || !preg_match('/^Bearer\s+(.*)$/', $authHeader, $matches)) {
        error_log("Auth: Cabeçalho de autorização não encontrado ou formato inválido");
        return false;
    }
    
    $token = $matches[1];
    error_log("Auth: Token encontrado: " . substr($token, 0, 10) . "...");
    
    // Para desenvolvimento, aceitar um token de exemplo para o usuário 1
    if ($token === "dev_token_user_1") {
        error_log("Auth: Token de desenvolvimento para usuário 1 aceito");
        return 1; // ID do usuário FUNDO MUNICIPAL DE SAUDE
    }
    
    // Ambiente de desenvolvimento: sempre retornar o usuário 1 para facilitar testes
    if (getenv('APP_ENV') === 'development') {
        error_log("Auth: Ambiente de desenvolvimento detectado, autorizando acesso como usuário 1");
        return 1;
    }
    
    // Validar o token
    try {
        // Conectar ao banco de dados
        $db = getDbConnection();
        
        // Verificar se a tabela de sessões existe
        $checkTable = $db->query("SHOW TABLES LIKE 'sessoes'");
        if ($checkTable->rowCount() === 0) {
            error_log("Auth: Tabela 'sessoes' não encontrada no banco de dados");
            // Para desenvolvimento, retornar usuário 1 se a tabela não existir
            return 1;
        }
        
        // Verificar se o token existe na tabela de sessões
        $stmt = $db->prepare("SELECT usuario_id FROM sessoes WHERE token = :token AND expiracao > NOW()");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            error_log("Auth: Usuário " . $result['usuario_id'] . " autenticado com sucesso");
            return $result['usuario_id'];
        }
        
        error_log("Auth: Token não encontrado ou expirado");
        return false;
    } catch (Exception $e) {
        error_log("Erro ao validar token: " . $e->getMessage());
        
        // Para desenvolvimento, retornar usuário 1 em caso de erro
        if (getenv('APP_ENV') === 'development') {
            error_log("Auth: Retornando usuário 1 para ambiente de desenvolvimento");
            return 1;
        }
        
        return false;
    }
}

/**
 * Verifica se o usuário é administrador
 * 
 * @param int $userId ID do usuário
 * @return bool true se o usuário for administrador, false caso contrário
 */
function checkAdmin($userId) {
    if (!$userId) {
        return false;
    }
    
    try {
        $db = getDbConnection();
        
        // Verificar se a tabela existe
        $checkTable = $db->query("SHOW TABLES LIKE 'usuarios'");
        if ($checkTable->rowCount() === 0) {
            error_log("Admin: Tabela 'usuarios' não encontrada");
            return false;
        }
        
        $stmt = $db->prepare("SELECT nivel_acesso FROM usuarios WHERE id = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['nivel_acesso'] === 'administrador';
    } catch (Exception $e) {
        error_log("Erro ao verificar admin: " . $e->getMessage());
        return false;
    }
} 