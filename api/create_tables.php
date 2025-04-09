<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Verificar se a tabela pagamentos já existe
    $stmt = $conn->query("SHOW TABLES LIKE 'pagamentos'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Tabela 'pagamentos' não existe. Criando...\n";
        
        // Criar a tabela pagamentos
        $sql = "CREATE TABLE pagamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            valor DECIMAL(10,2) NOT NULL,
            status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente',
            codigo_transacao VARCHAR(100),
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $conn->exec($sql);
        echo "Tabela 'pagamentos' criada com sucesso!\n";
        
        // Adicionar alguns dados de exemplo
        echo "Adicionando dados de exemplo...\n";
        
        $transactions = [
            [1, 100.00, 'pago', 'TRX' . uniqid()],
            [1, 50.00, 'pendente', 'TRX' . uniqid()],
            [1, 75.50, 'pago', 'TRX' . uniqid()],
            [1, 200.00, 'cancelado', 'TRX' . uniqid()],
            [1, 150.00, 'pago', 'TRX' . uniqid()]
        ];
        
        $sql = "INSERT INTO pagamentos (usuario_id, valor, status, codigo_transacao) VALUES (:usuario_id, :valor, :status, :codigo_transacao)";
        $stmt = $conn->prepare($sql);
        
        foreach ($transactions as $transaction) {
            $stmt->bindParam(':usuario_id', $transaction[0], PDO::PARAM_INT);
            $stmt->bindParam(':valor', $transaction[1]);
            $stmt->bindParam(':status', $transaction[2]);
            $stmt->bindParam(':codigo_transacao', $transaction[3]);
            $stmt->execute();
        }
        
        echo count($transactions) . " transações de exemplo adicionadas!\n";
    } else {
        echo "Tabela 'pagamentos' já existe!\n";
        
        // Contar registros
        $stmt = $conn->query("SELECT COUNT(*) FROM pagamentos");
        $count = $stmt->fetchColumn();
        
        echo "A tabela tem $count registros.\n";
        
        if ($count == 0) {
            echo "Adicionando dados de exemplo...\n";
            
            $transactions = [
                [1, 100.00, 'pago', 'TRX' . uniqid()],
                [1, 50.00, 'pendente', 'TRX' . uniqid()],
                [1, 75.50, 'pago', 'TRX' . uniqid()],
                [1, 200.00, 'cancelado', 'TRX' . uniqid()],
                [1, 150.00, 'pago', 'TRX' . uniqid()]
            ];
            
            $sql = "INSERT INTO pagamentos (usuario_id, valor, status, codigo_transacao) VALUES (:usuario_id, :valor, :status, :codigo_transacao)";
            $stmt = $conn->prepare($sql);
            
            foreach ($transactions as $transaction) {
                $stmt->bindParam(':usuario_id', $transaction[0], PDO::PARAM_INT);
                $stmt->bindParam(':valor', $transaction[1]);
                $stmt->bindParam(':status', $transaction[2]);
                $stmt->bindParam(':codigo_transacao', $transaction[3]);
                $stmt->execute();
            }
            
            echo count($transactions) . " transações de exemplo adicionadas!\n";
        }
    }
    
    echo "\nVerificando a estrutura atual da tabela 'pagamentos':\n";
    $stmt = $conn->query("DESCRIBE pagamentos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}