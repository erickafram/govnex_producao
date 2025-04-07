<?php
// Configuração do banco de dados
$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'govnex';
$dbPort = 3306;

try {
    // Conectar ao banco de dados
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $db = new PDO($dsn, $dbUser, $dbPassword, $options);
    
    // Verificar se a tabela já existe
    $checkTable = $db->query("SHOW TABLES LIKE 'api_tokens'");
    if ($checkTable->rowCount() > 0) {
        echo "Tabela 'api_tokens' já existe.\n";
    } else {
        // Criar a tabela
        $sql = "
            CREATE TABLE IF NOT EXISTS api_tokens (
              id INT AUTO_INCREMENT PRIMARY KEY,
              token VARCHAR(64) NOT NULL,
              description VARCHAR(255) NULL,
              user_id INT NULL,
              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              expires_at TIMESTAMP NULL,
              is_active BOOLEAN DEFAULT TRUE,
              FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE SET NULL,
              UNIQUE KEY (token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $db->exec($sql);
        echo "Tabela 'api_tokens' criada com sucesso.\n";
        
        // Criar um token padrão para testes
        $token = bin2hex(random_bytes(32));
        $description = "Token padrão para testes";
        
        $stmt = $db->prepare("
            INSERT INTO api_tokens (token, description, created_at, is_active)
            VALUES (:token, :description, NOW(), TRUE)
        ");
        
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':description', $description);
        $stmt->execute();
        
        echo "Token padrão criado: $token\n";
    }
    
    // Listar tokens existentes
    $stmt = $db->query("SELECT id, token, description, created_at, is_active FROM api_tokens");
    $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nTokens disponíveis:\n";
    foreach ($tokens as $token) {
        $status = $token['is_active'] ? 'Ativo' : 'Inativo';
        echo "ID: {$token['id']} | Token: " . substr($token['token'], 0, 20) . "... | Descrição: {$token['description']} | Status: $status\n";
    }
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
} 