<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

function testMySQLConnection() {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Testar uma query simples
        $stmt = $conn->query('SELECT NOW() as server_time');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'status' => 'success',
            'message' => 'Conexão com o banco de dados estabelecida com sucesso',
            'server_time' => $result['server_time'],
            'php_version' => PHP_VERSION,
            'pdo_drivers' => PDO::getAvailableDrivers(),
            'mysql_version' => $conn->getAttribute(PDO::ATTR_SERVER_VERSION),
            'connection_status' => $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS)
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Falha na conexão com o banco de dados',
            'error_details' => $e->getMessage(),
            'php_version' => PHP_VERSION,
            'pdo_drivers' => PDO::getAvailableDrivers()
        ];
    }
}

echo json_encode(testMySQLConnection(), JSON_PRETTY_PRINT);