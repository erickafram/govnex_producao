<?php
// Script para verificar a tabela de sessões

require_once __DIR__ . '/config.php';

try {
    $db = getDbConnection();
    
    // Verificar se a tabela existe
    $tableExists = $db->query("SHOW TABLES LIKE 'sessoes'")->rowCount() > 0;
    echo "Tabela sessoes existe: " . ($tableExists ? "Sim" : "Não") . "\n";
    
    if (!$tableExists) {
        echo "Criando tabela de sessões...\n";
        
        $createTable = "
        CREATE TABLE `sessoes` (
          `id` int NOT NULL AUTO_INCREMENT,
          `usuario_id` int NOT NULL,
          `token` varchar(255) NOT NULL,
          `expiracao` datetime NOT NULL,
          `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_token` (`token`),
          KEY `idx_usuario_id` (`usuario_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        ";
        
        $db->exec($createTable);
        echo "Tabela 'sessoes' criada com sucesso.\n";
    } else {
        // Verificar estrutura da tabela
        echo "Verificando estrutura da tabela 'sessoes'...\n";
        $columns = $db->query("SHOW COLUMNS FROM sessoes")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Colunas na tabela 'sessoes':\n";
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
        
        // Contar registros
        $count = $db->query("SELECT COUNT(*) FROM sessoes")->fetchColumn();
        echo "Total de registros: $count\n";
        
        // Extrair informações da sessão do usuário logado
        echo "Buscando tokens de sessão ativos...\n";
        $stmt = $db->query("SELECT * FROM sessoes WHERE expiracao > NOW() ORDER BY id DESC LIMIT 10");
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($sessions) > 0) {
            echo "Sessões ativas encontradas:\n";
            echo str_repeat("-", 80) . "\n";
            echo sprintf("%-5s | %-10s | %-40s | %-20s\n", 
                    "ID", "Usuario", "Token", "Expiracao");
            echo str_repeat("-", 80) . "\n";
            
            foreach ($sessions as $session) {
                echo sprintf("%-5s | %-10s | %-40s | %-20s\n", 
                    $session['id'],
                    $session['usuario_id'],
                    substr($session['token'], 0, 37) . "...",
                    $session['expiracao']
                );
            }
            echo str_repeat("-", 80) . "\n";
        } else {
            echo "Nenhuma sessão ativa encontrada.\n";
            
            // Adicionar uma sessão para teste se não houver nenhuma
            echo "Inserindo uma sessão de teste para o usuário 1...\n";
            
            $token = bin2hex(random_bytes(32));
            $expiracao = date('Y-m-d H:i:s', strtotime('+7 days'));
            
            $stmt = $db->prepare("INSERT INTO sessoes (usuario_id, token, expiracao) VALUES (?, ?, ?)");
            $stmt->execute([1, $token, $expiracao]);
            
            echo "Sessão de teste criada com token: $token\n";
            echo "Token expira em: $expiracao\n";
            echo "Use este token para testes de autenticação.\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
} 