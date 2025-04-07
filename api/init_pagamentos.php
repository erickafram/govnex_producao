<?php
// Script para inicializar a tabela de pagamentos

require_once __DIR__ . '/config.php';

// Conectar ao banco de dados
try {
    $db = getDbConnection();
    echo "Conectado ao banco de dados com sucesso.\n";
    
    // Verificar se a tabela já existe
    $checkTable = $db->query("SHOW TABLES LIKE 'pagamentos'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if ($tableExists) {
        echo "A tabela 'pagamentos' já existe.\n";
    } else {
        // Criar a tabela pagamentos
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
        echo "Tabela 'pagamentos' criada com sucesso.\n";
        
        // Adicionar alguns registros de exemplo
        $insertData = "
        INSERT INTO `pagamentos` (`usuario_id`, `valor`, `status`, `codigo_transacao`) VALUES
        (1, 200.00, 'pago', '3f8e4c72-5272-489e-be80-eb03b000463a'),
        (1, 100.00, 'pago', '5e9a1c36-8742-4d98-b3a1-f25c87b612e9'),
        (2, 150.00, 'pago', '7d8e1a52-963c-4b78-a5f2-c9e74d3b89f1'),
        (2, 75.00, 'pendente', '9c3b2a18-5d46-4e93-8c17-6a91f3d25e2b');
        ";
        
        $db->exec($insertData);
        echo "Dados de exemplo inseridos com sucesso.\n";
    }
    
    // Verificar se há dados na tabela
    $countRecords = $db->query("SELECT COUNT(*) FROM pagamentos")->fetchColumn();
    echo "A tabela 'pagamentos' contém $countRecords registros.\n";
    
    echo "Inicialização concluída com sucesso.\n";
    
} catch (PDOException $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

// Para executar este script, acesse-o diretamente no navegador ou via linha de comando:
// php init_pagamentos.php 