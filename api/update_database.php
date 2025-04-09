<?php
// Configurações iniciais
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir configuração do banco de dados
require_once __DIR__ . '/config/database.php';

// Função para executar queries SQL
function executarQuery($conn, $query, $descricao) {
    try {
        $result = $conn->exec($query);
        echo "<p>✅ {$descricao}: Executado com sucesso. ({$result} linhas afetadas)</p>";
        return true;
    } catch (PDOException $e) {
        echo "<p>❌ {$descricao}: Erro - " . $e->getMessage() . "</p>";
        return false;
    }
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Atualização do Banco de Dados</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .container { max-width: 800px; margin: 0 auto; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Atualização do Banco de Dados</h1>";

try {
    // Criar conexão com o banco de dados
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<h2>Verificando tabela api_tokens</h2>";
    
    // Verificar se a tabela existe
    $stmt = $conn->query("SHOW TABLES LIKE 'api_tokens'");
    $tabelaExiste = $stmt->rowCount() > 0;
    
    if (!$tabelaExiste) {
        echo "<p class='error'>A tabela api_tokens não existe. Executando script de criação...</p>";
        
        // Script para criar a tabela
        $sql = "CREATE TABLE api_tokens (
            id INT(11) NOT NULL AUTO_INCREMENT,
            token VARCHAR(255) NOT NULL,
            description TEXT NULL DEFAULT NULL,
            user_id INT(11) NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            last_used TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE INDEX token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        
        executarQuery($conn, $sql, "Criação da tabela api_tokens");
    } else {
        echo "<p class='success'>A tabela api_tokens já existe.</p>";
        
        // Verificar e adicionar colunas necessárias
        echo "<h3>Verificando colunas da tabela api_tokens</h3>";
        
        // Obter as colunas existentes
        $stmt = $conn->query("SHOW COLUMNS FROM api_tokens");
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        // Definir as colunas que devem existir e seus tipos
        $colunasNecessarias = [
            'last_used' => "ADD COLUMN last_used TIMESTAMP NULL DEFAULT NULL AFTER is_active"
        ];
        
        // Verificar cada coluna e adicionar se necessário
        foreach ($colunasNecessarias as $coluna => $sql) {
            if (!in_array($coluna, $colunas)) {
                echo "<p>Coluna '{$coluna}' não existe. Adicionando...</p>";
                $alterQuery = "ALTER TABLE api_tokens {$sql}";
                executarQuery($conn, $alterQuery, "Adição da coluna {$coluna}");
            } else {
                echo "<p class='success'>Coluna '{$coluna}' já existe.</p>";
            }
        }
    }
    
    // Verificar a tabela de consultas_log
    echo "<h2>Verificando tabela consultas_log</h2>";
    
    $stmt = $conn->query("SHOW TABLES LIKE 'consultas_log'");
    $tabelaExiste = $stmt->rowCount() > 0;
    
    if (!$tabelaExiste) {
        echo "<p class='error'>A tabela consultas_log não existe. Executando script de criação...</p>";
        
        // Script para criar a tabela
        $sql = "CREATE TABLE consultas_log (
            id INT(11) NOT NULL AUTO_INCREMENT,
            cnpj_consultado VARCHAR(20) NOT NULL,
            dominio_origem VARCHAR(255) NOT NULL,
            data_consulta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            custo DECIMAL(10,2) NOT NULL DEFAULT 0.12,
            usuario_id INT(11) NULL DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        
        executarQuery($conn, $sql, "Criação da tabela consultas_log");
    } else {
        echo "<p class='success'>A tabela consultas_log já existe.</p>";
        
        // Verificar e adicionar colunas necessárias
        echo "<h3>Verificando colunas da tabela consultas_log</h3>";
        
        // Obter as colunas existentes
        $stmt = $conn->query("SHOW COLUMNS FROM consultas_log");
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        // Definir as colunas que devem existir e seus tipos
        $colunasNecessarias = [
            'usuario_id' => "ADD COLUMN usuario_id INT(11) NULL DEFAULT NULL AFTER custo"
        ];
        
        // Verificar cada coluna e adicionar se necessário
        foreach ($colunasNecessarias as $coluna => $sql) {
            if (!in_array($coluna, $colunas)) {
                echo "<p>Coluna '{$coluna}' não existe. Adicionando...</p>";
                $alterQuery = "ALTER TABLE consultas_log {$sql}";
                executarQuery($conn, $alterQuery, "Adição da coluna {$coluna}");
            } else {
                echo "<p class='success'>Coluna '{$coluna}' já existe.</p>";
            }
        }
    }
    
    echo "<h2>Processo de atualização concluído</h2>";
    echo "<p>O banco de dados foi verificado e atualizado com sucesso.</p>";
    
} catch (PDOException $e) {
    echo "<h2 class='error'>Erro na conexão com o banco de dados</h2>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<h2 class='error'>Erro durante a atualização</h2>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
}

echo "</div></body></html>"; 