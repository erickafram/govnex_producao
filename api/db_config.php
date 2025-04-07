<?php
// Detect environment based on server name
function getDbConfig() {
    // Check if we're in production (Digital Ocean server)
    $isProduction = (strpos($_SERVER['SERVER_NAME'], '161.35.60.249') !== false || 
                    strpos($_SERVER['SERVER_NAME'], 'govnex.site') !== false);
    
    if ($isProduction) {
        // Production database configuration
        return [
            'host' => 'localhost',  // Usually 'localhost' even in production
            'dbname' => 'govnex',   // Database name in production
            'username' => 'govnex', // Production username (adjust as needed)
            'password' => '@@2025@@Ekb' // Production password (adjust as needed)
        ];
    } else {
        // Local development database configuration
        return [
            'host' => 'localhost',
            'dbname' => 'govnex',
            'username' => 'root',
            'password' => ''
        ];
    }
}

// Function to get database connection
function getDbConnection() {
    $config = getDbConfig();
    
    try {
        $conn = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8", 
            $config['username'], 
            $config['password']
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        // Log the error but don't expose details
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}
