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
        // Create logs directory if it doesn't exist
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
            chmod($logDir, 0777); // Ensure directory is writable
        }

        // Detect environment - check for server IP, hostname, or use a fallback to .env file
        $this->isProduction = false;
        
        if (isset($_SERVER['SERVER_NAME']) && 
            (strpos($_SERVER['SERVER_NAME'], '161.35.60.249') !== false || 
             strpos($_SERVER['SERVER_NAME'], 'govnex.site') !== false)) {
            $this->isProduction = true;
        } elseif (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] === '161.35.60.249') {
            $this->isProduction = true;
        } elseif (isset($_SERVER['HTTP_HOST']) && 
            (strpos($_SERVER['HTTP_HOST'], '161.35.60.249') !== false || 
             strpos($_SERVER['HTTP_HOST'], 'govnex.site') !== false)) {
            $this->isProduction = true;
        } elseif (file_exists(__DIR__ . '/../../.env.production')) {
            // If a production env file exists, we're likely in production
            $this->isProduction = true;
        }

        // Load environment variables
        $this->loadEnvVars();
        
        // Log configuration
        $this->logDebugInfo();
    }

    private function loadEnvVars() {
        // Check for production environment file first
        $prodEnvFile = __DIR__ . '/../.env.production';
        $devEnvFile = __DIR__ . '/../.env';
        
        // Set default values
        if ($this->isProduction) {
            // Default production values
            $this->host = 'localhost';
            $this->db_name = 'govnex';
            $this->username = 'root';
            $this->password = 'Senha@Forte2025!';
            $this->port = '3306';
            
            // Try to load from production env file
            if (file_exists($prodEnvFile)) {
                $this->loadFromEnvFile($prodEnvFile);
            } elseif (file_exists($devEnvFile)) {
                // Fall back to dev env if production doesn't exist
                $this->loadFromEnvFile($devEnvFile);
            }
        } else {
            // Default development values for local WAMP/XAMPP
            $this->host = 'localhost';
            $this->db_name = 'govnex';
            $this->username = 'root';
            $this->password = 'root@@2025@@';
            $this->port = '3306';
            
            // Try to load from dev env file
            if (file_exists($devEnvFile)) {
                $this->loadFromEnvFile($devEnvFile);
            }
        }
        
        $this->logMessage("Database configuration loaded. Host: {$this->host}, DB: {$this->db_name}, User: {$this->username}, Port: {$this->port}, Production: " . ($this->isProduction ? 'Yes' : 'No'));
    }
    
    private function loadFromEnvFile($envFile) {
        $this->logMessage("Loading configuration from file: $envFile");
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Process environment variables
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if they exist
                if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                }
                
                // Configure variables based on name
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

    private function logDebugInfo() {
        $logFile = __DIR__ . '/../logs/db_log.txt';
        
        // Debug information
        $debugInfo = [
            date('Y-m-d H:i:s') . " - Database connection initialization",
            "Environment: " . ($this->isProduction ? "Production" : "Development"),
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
            // Build DSN
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            
            // PDO options
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            // Try connection
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Log success
            $this->logMessage("Database connection successful");
            
            return $this->conn;
        } catch(PDOException $e) {
            // Log detailed error
            $errorMessage = "Connection error: " . $e->getMessage();
            $this->logMessage($errorMessage);
            $this->logMessage("DSN: mysql:host={$this->host};port={$this->port};dbname={$this->db_name}");
            $this->logMessage("Username: {$this->username}");
            
            // In development, show real error
            if (!$this->isProduction) {
                throw new PDOException($errorMessage);
            }
            
            // In production, return generic message
            throw new PDOException("Database connection failed");
        }
    }

    private function logMessage($message) {
        $logFile = __DIR__ . '/../logs/db_log.txt';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
}