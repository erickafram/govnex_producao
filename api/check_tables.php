<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Listar todas as tabelas no banco de dados
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tabelas encontradas no banco de dados:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Verificar se a tabela pagamentos existe
    $pagamentosExists = in_array('pagamentos', $tables);
    echo "\nTabela 'pagamentos' existe: " . ($pagamentosExists ? 'SIM' : 'NÃO') . "\n";
    
    // Se a tabela pagamentos existir, mostrar sua estrutura
    if ($pagamentosExists) {
        $stmt = $conn->query("DESCRIBE pagamentos");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nEstrutura da tabela 'pagamentos':\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
} 