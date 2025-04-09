<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    private $port;
    private $isProduction;

    public function __construct() {
        // Criar diretório de logs se não existir
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Detectar ambiente
        $this->isProduction = (
            isset($_SERVER['SERVER_NAME']) && 
            (strpos($_SERVER['SERVER_NAME'], '161.35.60.249') !== false || 
             strpos($_SERVER['SERVER_NAME'], 'govnex.site') !== false)
        );

        // Carregar variáveis do arquivo .env na raiz
        $this->loadEnvVars();
        
        // Log das configurações
        $this->logDebugInfo();
    }

    private function loadEnvVars() {
        // Selecionar o arquivo .env apropriado
        $envFile = $this->isProduction 
            ? __DIR__ . '/../../.env.production' 
            : __DIR__ . '/../../.env';
        
        // Valores padrão para desenvolvimento local (WAMP/XAMPP)
        if (!$this->isProduction) {
            // No ambiente local, sempre usar root com senha vazia
            $this->host = 'localhost';
            $this->db_name = 'govnex';
            $this->username = 'root';
            $this->password = '';
            $this->port = '3306';
        } else {
            // Valores padrão para produção
            $this->host = 'localhost';
            $this->db_name = 'govnex';
            $this->username = 'govnex';
            $this->password = '@@2025@@Ekb';
            $this->port = '3306';
        }
        
        // Se existir arquivo .env, leia as configurações dele
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Ignorar comentários
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Processar variáveis de ambiente
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    
                    // Remover aspas se existirem
                    if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                        $value = substr($value, 1, -1);
                    } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                        $value = substr($value, 1, -1);
                    }
                    
                    // Configurar variáveis baseadas no nome
                    // Em ambiente local, ignorar as configurações de usuário do .env
                    if (!$this->isProduction) {
                        switch($name) {
                            case 'DB_HOST':
                                $this->host = $value;
                                break;
                            case 'DB_NAME':
                                $this->db_name = $value;
                                break;
                            case 'DB_PORT':
                                $this->port = $value;
                                break;
                            // Não atualizar username e password em ambiente local
                        }
                    } else {
                        // Em produção, usar todas as configurações do .env
                        switch($name) {
                            case 'DB_HOST':
                                $this->host = $value;
                                break;
                            case 'DB_NAME':
                                $this->db_name = $value;
                                break;
                            case 'DB_USER':
                                $this->username = $value;
                                break;
                            case 'DB_PASSWORD':
                                $this->password = $value;
                                break;
                            case 'DB_PORT':
                                $this->port = $value;
                                break;
                        }
                    }
                }
            }
        }
    }

    private function logDebugInfo() {
        $logFile = __DIR__ . '/../logs/db_log.txt';
        
        // Informações para debug
        $debugInfo = [
            date('Y-m-d H:i:s') . " - Iniciando conexão com o banco de dados",
            "Ambiente: " . ($this->isProduction ? "Produção" : "Desenvolvimento"),
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
            $errorMessage = "Erro de conexão: " . $e->getMessage();
            $this->logMessage($errorMessage);
            
            // Em ambiente de desenvolvimento, mostrar o erro real
            if (!$this->isProduction) {
                throw new PDOException($errorMessage);
            }
            
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