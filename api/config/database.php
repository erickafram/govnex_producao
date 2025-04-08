<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    private $port;

    public function __construct() {
        // Criar diretório de logs se não existir
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Configurações fixas para produção
        $this->host = '127.0.0.1'; // Use IP em vez de 'localhost'
        $this->db_name = 'govnex';
        $this->username = 'govnex';
        $this->password = '@@2025@@Ekb';
        $this->port = 3306;

        // Log das configurações
        $this->logDebugInfo();
    }

    private function logDebugInfo() {
        $logFile = __DIR__ . '/../logs/db_log.txt';
        
        // Informações para debug
        $debugInfo = [
            date('Y-m-d H:i:s') . " - Iniciando conexão com o banco de dados",
            "Host: " . $this->host,
            "Database: " . $this->db_name,
            "Username: " . $this->username,
            "Port: " . $this->port,
            "PHP Version: " . PHP_VERSION,
            "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'undefined'),
            "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'undefined'),
            "Remote Addr: " . ($_SERVER['REMOTE_ADDR'] ?? 'undefined')
        ];
        
        file_put_contents($logFile, implode("\n", $debugInfo) . "\n\n", FILE_APPEND);
    }

    public function getConnection() {
        try {
            // Construir DSN
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            
            // Opções do PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            // Tentar conexão
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Log de sucesso
            $this->logMessage("Conexão estabelecida com sucesso");
            
            return $this->conn;
        } catch(PDOException $e) {
            // Log do erro completo
            $this->logMessage("Erro de conexão: " . $e->getMessage());
            
            // Em produção, retornar mensagem genérica
            throw new PDOException("Falha na conexão com o banco de dados");
        }
    }

    private function logMessage($message) {
        $logFile = __DIR__ . '/../logs/db_log.txt';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
}