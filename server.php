<?php
// Carregar variáveis de ambiente
require_once __DIR__ . '/db_config.php'';

// Obter a porta da API do arquivo .env ou usar 8000 como padrão
$port = getenv('API_PORT') ?: 8000;

// Iniciar o servidor PHP
echo "Iniciando servidor PHP na porta {$port}...\n";
echo "Acesse: http://localhost:{$port}\n";
echo "Pressione Ctrl+C para parar o servidor.\n";

// Comando para iniciar o servidor
$command = "php -S localhost:{$port} -t " . __DIR__;

// Executar o comando
passthru($command);
