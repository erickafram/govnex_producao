<?php
// Script para verificar o estado atual da tabela pagamentos

require_once __DIR__ . '/db_config.php'''';

try {
    $db = getDbConnection();
    
    // Verificar se a tabela existe
    $tableExists = $db->query("SHOW TABLES LIKE 'pagamentos'")->rowCount() > 0;
    echo "Tabela pagamentos existe: " . ($tableExists ? "Sim" : "Não") . "\n";
    
    if ($tableExists) {
        // Contar registros
        $count = $db->query("SELECT COUNT(*) FROM pagamentos")->fetchColumn();
        echo "Total de registros: $count\n";
        
        // Listar todos os registros
        $records = $db->query("SELECT * FROM pagamentos ORDER BY data_criacao DESC")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Registros:\n";
        echo str_repeat("-", 80) . "\n";
        echo sprintf("%-5s | %-10s | %-10s | %-10s | %-36s | %-19s\n", 
                "ID", "Usuario", "Valor", "Status", "Código", "Data");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($records as $record) {
            echo sprintf("%-5s | %-10s | %-10s | %-10s | %-36s | %-19s\n", 
                $record['id'],
                $record['usuario_id'],
                $record['valor'],
                $record['status'],
                $record['codigo_transacao'],
                $record['data_criacao']
            );
        }
        
        echo str_repeat("-", 80) . "\n";
        
        // Adicionar um novo registro para teste
        $newId = rand(1, 3); // ID de usuário aleatório entre 1 e 3
        $valor = rand(50, 500); // Valor aleatório entre 50 e 500
        $status = ['pendente', 'pago', 'cancelado'][rand(0, 2)]; // Status aleatório
        $codigo = 'test-' . uniqid();
        
        $stmt = $db->prepare("INSERT INTO pagamentos (usuario_id, valor, status, codigo_transacao) VALUES (?, ?, ?, ?)");
        $stmt->execute([$newId, $valor, $status, $codigo]);
        
        $insertId = $db->lastInsertId();
        echo "Novo registro adicionado: ID=$insertId, Usuario=$newId, Valor=$valor, Status=$status, Código=$codigo\n";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
} 
