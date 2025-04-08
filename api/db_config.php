<?php
// Arquivo de configuração do banco de dados
// Apenas definições específicas do banco de dados

// Verifica se a função já existe antes de declará-la
if (!function_exists('getDbConfig')) {
    function getDbConfig() {
        return [
            'host' => 'localhost',
            'dbname' => 'govnex',
            'username' => 'govnex',
            'password' => '@@2025@@Ekb'
        ];
    }
}

// Não declaramos getDbConnection aqui, pois já existe em config.php
// Isso evita o erro "Cannot redeclare getDbConnection()"

// Database connection function - only define if not already defined elsewhere
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        $config = getDbConfig();
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
            
            // Test the connection with a simple query
            $pdo->query('SELECT 1');
            
            return $pdo;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            // Return null on failure to allow proper error handling
            return null;
        }
    }
}
