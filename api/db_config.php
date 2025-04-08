<?php
// Arquivo de configuração do banco de dados
// Apenas definições específicas do banco de dados

// Verifica se a função já existe antes de declará-la
if (!function_exists('getDbConfig')) {
    function getDbConfig() {
        return [
            'host' => 'localhost',
            'dbname' => 'govnex',
            'username' => 'root',
            'password' => ''
        ];
    }
}

// Não declaramos getDbConnection aqui, pois já existe em config.php
// Isso evita o erro "Cannot redeclare getDbConnection()"
