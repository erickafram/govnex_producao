<?php
// Arquivo de configuração do banco de dados
// Este arquivo existe apenas para compatibilidade com código legado

// Incluir a classe Database
require_once __DIR__ . '/config/database.php';

// Verifica se a função já existe antes de declará-la
if (!function_exists('getDbConfig')) {
    function getDbConfig() {
        // Usar uma instância da classe Database para obter as configurações
        $database = new Database();
        $reflect = new ReflectionClass($database);
        
        // Acessar as propriedades privadas
        $host = $reflect->getProperty('host');
        $db_name = $reflect->getProperty('db_name');
        $username = $reflect->getProperty('username');
        $password = $reflect->getProperty('password');
        $port = $reflect->getProperty('port');
        
        $host->setAccessible(true);
        $db_name->setAccessible(true);
        $username->setAccessible(true);
        $password->setAccessible(true);
        $port->setAccessible(true);
        
        return [
            'host' => $host->getValue($database),
            'dbname' => $db_name->getValue($database),
            'username' => $username->getValue($database),
            'password' => $password->getValue($database),
            'port' => $port->getValue($database)
        ];
    }
}

// Função para obter conexão com o banco de dados (compatibilidade com código legado)
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        // Simplesmente usar a classe Database
        $database = new Database();
        return $database->getConnection();
    }
}
