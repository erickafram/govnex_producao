<?php
// Script para adicionar alguns pagamentos de exemplo para o usuário 1

require_once __DIR__ . '/config.php';

try {
    $db = getDbConnection();
    
    // Verificar se a tabela pagamentos existe
    $tableExists = $db->query("SHOW TABLES LIKE 'pagamentos'")->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Criando tabela pagamentos...\n";
        
        $createTable = "
        CREATE TABLE `pagamentos` (
          `id` int NOT NULL AUTO_INCREMENT,
          `usuario_id` int NOT NULL,
          `valor` decimal(10,2) NOT NULL,
          `status` enum('pendente','pago','cancelado') DEFAULT 'pendente',
          `codigo_transacao` varchar(255) NOT NULL,
          `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
          `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_usuario_id` (`usuario_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        ";
        
        $db->exec($createTable);
        echo "Tabela pagamentos criada com sucesso.\n";
    }
    
    // Verificar registros existentes
    $count = $db->query("SELECT COUNT(*) FROM pagamentos WHERE usuario_id = 1")->fetchColumn();
    echo "Usuário 1 já possui $count pagamentos registrados.\n";
    
    if ($count < 5) {
        echo "Adicionando pagamentos de exemplo para o usuário 1...\n";
        
        // Data dos pagamentos (do mais recente ao mais antigo)
        $dates = [
            date('Y-m-d H:i:s', strtotime('-1 day')),
            date('Y-m-d H:i:s', strtotime('-5 days')),
            date('Y-m-d H:i:s', strtotime('-12 days')),
            date('Y-m-d H:i:s', strtotime('-20 days')),
            date('Y-m-d H:i:s', strtotime('-30 days'))
        ];
        
        // Valores dos pagamentos
        $values = [50.00, 100.00, 200.00, 150.00, 75.00];
        
        // Status possíveis
        $statuses = ['pago', 'pago', 'pago', 'pendente', 'cancelado'];
        
        // Inserir os pagamentos
        $stmt = $db->prepare("
            INSERT INTO pagamentos 
            (usuario_id, valor, status, codigo_transacao, data_criacao) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $inserted = 0;
        
        for ($i = 0; $i < 5; $i++) {
            // Gerar código de transação único
            $code = bin2hex(random_bytes(16));
            
            try {
                $stmt->execute([1, $values[$i], $statuses[$i], $code, $dates[$i]]);
                $inserted++;
                echo "Pagamento $inserted: R$ {$values[$i]} - {$statuses[$i]} - {$dates[$i]}\n";
            } catch (PDOException $e) {
                // Ignorar erros de duplicação
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }
        
        echo "Adicionados $inserted novos pagamentos.\n";
    } else {
        echo "Já existem pagamentos suficientes. Pulando criação de exemplos.\n";
    }
    
    // Listar todos os pagamentos do usuário 1
    echo "\nListando todos os pagamentos do usuário 1:\n";
    echo str_repeat("-", 80) . "\n";
    echo sprintf("%-5s | %-10s | %-10s | %-36s | %-20s\n", 
            "ID", "Valor", "Status", "Código", "Data");
    echo str_repeat("-", 80) . "\n";
    
    $payments = $db->query("
        SELECT id, valor, status, codigo_transacao, data_criacao 
        FROM pagamentos 
        WHERE usuario_id = 1 
        ORDER BY data_criacao DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($payments as $payment) {
        echo sprintf("%-5s | %-10s | %-10s | %-36s | %-20s\n", 
            $payment['id'],
            'R$ ' . number_format($payment['valor'], 2, ',', '.'),
            $payment['status'],
            $payment['codigo_transacao'],
            $payment['data_criacao']
        );
    }
    echo str_repeat("-", 80) . "\n";
    
    echo "\nProcesso concluído com sucesso!\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
} 