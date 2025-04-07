<?php
// Arquivo de teste para diagnosticar problemas com o endpoint transactions.php

// Definir cabeçalhos para resposta JSON
header('Content-Type: application/json');

// Incluir arquivos necessários
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/Transaction.php';

// Testar conexão com o banco de dados
try {
    $db = getDbConnection();
    $dbConnected = true;
} catch (Exception $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
}

// Verificar se a tabela pagamentos existe
$tableExists = false;
$numRecords = 0;

if ($dbConnected) {
    try {
        $stmt = $db->query("SHOW TABLES LIKE 'pagamentos'");
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            $stmt = $db->query("SELECT COUNT(*) FROM pagamentos");
            $numRecords = $stmt->fetchColumn();
        }
    } catch (Exception $e) {
        $tableError = $e->getMessage();
    }
}

// Responder com informações de diagnóstico
echo json_encode([
    'success' => true,
    'diagnostics' => [
        'db_connected' => $dbConnected,
        'db_error' => $dbError ?? null,
        'table_exists' => $tableExists,
        'table_error' => $tableError ?? null,
        'num_records' => $numRecords,
        'php_version' => PHP_VERSION,
        'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    ]
]); 