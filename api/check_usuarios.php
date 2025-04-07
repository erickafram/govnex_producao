<?php
// Script para verificar a tabela de usuários

require_once __DIR__ . '/config.php';

try {
    $db = getDbConnection();
    
    // Verificar se a tabela existe
    $tableExists = $db->query("SHOW TABLES LIKE 'usuarios'")->rowCount() > 0;
    echo "Tabela usuarios existe: " . ($tableExists ? "Sim" : "Não") . "\n";
    
    if ($tableExists) {
        // Contar registros
        $count = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        echo "Total de registros: $count\n";
        
        // Listar os primeiros 5 usuários
        echo "Listando usuários:\n";
        $stmt = $db->query("SELECT id, nome, email, nivel_acesso, data_cadastro, credito FROM usuarios LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo str_repeat("-", 100) . "\n";
        echo sprintf("%-5s | %-30s | %-30s | %-15s | %-20s | %-10s\n", 
                "ID", "Nome", "Email", "Nível", "Data Cadastro", "Crédito");
        echo str_repeat("-", 100) . "\n";
        
        foreach ($users as $user) {
            echo sprintf("%-5s | %-30s | %-30s | %-15s | %-20s | %-10s\n", 
                $user['id'],
                substr($user['nome'], 0, 28) . (strlen($user['nome']) > 28 ? '..' : ''),
                substr($user['email'], 0, 28) . (strlen($user['email']) > 28 ? '..' : ''),
                $user['nivel_acesso'],
                $user['data_cadastro'],
                $user['credito']
            );
        }
        echo str_repeat("-", 100) . "\n";
    }
    
    // Verificar se a tabela sessoes existe
    $sessoesExists = $db->query("SHOW TABLES LIKE 'sessoes'")->rowCount() > 0;
    echo "Tabela sessoes existe: " . ($sessoesExists ? "Sim" : "Não") . "\n";
    
    if ($sessoesExists) {
        // Contar registros
        $countSessoes = $db->query("SELECT COUNT(*) FROM sessoes")->fetchColumn();
        echo "Total de sessões: $countSessoes\n";
        
        // Verificar se há sessões para o usuário 1
        $stmt = $db->prepare("SELECT COUNT(*) FROM sessoes WHERE usuario_id = ?");
        $stmt->execute([1]);
        $user1Sessions = $stmt->fetchColumn();
        
        echo "Sessões para usuário ID 1: $user1Sessions\n";
        
        if ($user1Sessions == 0 && $count > 0) {
            // Criar uma sessão para o primeiro usuário
            $firstUserId = $db->query("SELECT id FROM usuarios ORDER BY id LIMIT 1")->fetchColumn();
            
            echo "Criando sessão para o usuário ID $firstUserId...\n";
            
            $token = bin2hex(random_bytes(32));
            $expiracao = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            $stmt = $db->prepare("INSERT INTO sessoes (usuario_id, token, expiracao) VALUES (?, ?, ?)");
            $stmt->execute([$firstUserId, $token, $expiracao]);
            
            echo "Sessão criada com token: $token\n";
            echo "Token expira em: $expiracao\n";
            echo "Use este token para testes.\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 