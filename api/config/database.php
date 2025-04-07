<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Detectar ambiente
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $isProduction = ($serverName === '161.35.60.249' || $serverName === 'govnex.site' || strpos($serverName, '.govnex.site') !== false);
        
        // Configurar conexão com base no ambiente
        if ($isProduction) {
            // Configurações de produção
            $this->host = "localhost"; // Normalmente continua sendo localhost em produção
            $this->db_name = "govnex";
            $this->username = "root"; // Substitua pelo usuário de produção
            $this->password = ""; // Substitua pela senha de produção
        } else {
            // Configurações de desenvolvimento
            $this->host = "localhost";
            $this->db_name = "govnex";
            $this->username = "root";
            $this->password = "";
        }
        
        // Log para depuração
        $logFile = __DIR__ . '/../db_log.txt';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ambiente: " . ($isProduction ? "Produção" : "Desenvolvimento") . "\n", FILE_APPEND);
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            
            // Log para depuração
            $logFile = __DIR__ . '/../db_log.txt';
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Conexão com o banco de dados estabelecida com sucesso\n", FILE_APPEND);
        } catch(PDOException $exception) {
            // Log do erro
            $logFile = __DIR__ . '/../db_log.txt';
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erro de conexão: " . $exception->getMessage() . "\n", FILE_APPEND);
            
            // Em vez de imprimir o erro, lançamos uma exceção para ser tratada pelo código que chamou este método
            throw new PDOException("Erro de conexão com o banco de dados: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
