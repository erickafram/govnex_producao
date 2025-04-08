<?php
// Configurações de cabeçalho para acesso direto
header("Content-Type: text/html; charset=UTF-8");

// Incluir arquivo de configuração do banco de dados
require_once __DIR__ . '/config/database.php';

// Função para executar consultas SQL
function executeSql($db, $sql, $description) {
    echo "<p>Tentando: $description... ";
    try {
        $db->exec($sql);
        echo "<span style='color:green'>Sucesso!</span></p>";
        return true;
    } catch (PDOException $e) {
        echo "<span style='color:red'>Erro: " . $e->getMessage() . "</span></p>";
        return false;
    }
}

// Conectar ao banco de dados
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "<h2>Conexão com o banco de dados estabelecida com sucesso!</h2>";
} catch (Exception $e) {
    die("<h2 style='color:red'>Erro ao conectar ao banco de dados: " . $e->getMessage() . "</h2>");
}

// Criar tabela payments se não existir
$createPaymentsTable = "
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pendente','pago','cancelado') NOT NULL DEFAULT 'pendente',
  `transaction_id` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

executeSql($db, $createPaymentsTable, "Criar tabela payments");

// Verificar se a tabela users existe
$checkUsersTable = "SHOW TABLES LIKE 'users'";
$stmt = $db->prepare($checkUsersTable);
$stmt->execute();
$usersTableExists = $stmt->rowCount() > 0;

if (!$usersTableExists) {
    echo "<p style='color:orange'>A tabela 'users' não existe. Criando...</p>";
    
    $createUsersTable = "
    CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    executeSql($db, $createUsersTable, "Criar tabela users");
    
    // Inserir usuário de teste
    $insertTestUser = "
    INSERT INTO `users` (`name`, `email`, `password`, `balance`, `created_at`, `updated_at`)
    VALUES ('Usuário Teste', 'teste@exemplo.com', '" . password_hash('senha123', PASSWORD_DEFAULT) . "', 0.00, NOW(), NOW());
    ";
    
    executeSql($db, $insertTestUser, "Inserir usuário de teste");
}

// Verificar se a tabela transactions existe
$checkTransactionsTable = "SHOW TABLES LIKE 'transactions'";
$stmt = $db->prepare($checkTransactionsTable);
$stmt->execute();
$transactionsTableExists = $stmt->rowCount() > 0;

if (!$transactionsTableExists) {
    echo "<p style='color:orange'>A tabela 'transactions' não existe. Criando...</p>";
    
    $createTransactionsTable = "
    CREATE TABLE IF NOT EXISTS `transactions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `amount` decimal(10,2) NOT NULL,
      `type` enum('credit','debit') NOT NULL,
      `description` text,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    executeSql($db, $createTransactionsTable, "Criar tabela transactions");
}

echo "<h3>Configuração do banco de dados concluída!</h3>";
echo "<p>Você pode voltar para a aplicação e tentar novamente.</p>";
?>
