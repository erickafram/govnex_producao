<?php
// Configurações de CORS para o servidor PHP
$host = '0.0.0.0';
$port = 8000;
$docRoot = __DIR__; // Pasta atual (api)

// Arquivo de log
$logFile = __DIR__ . '/cors_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Iniciando servidor CORS na porta {$port}\n", FILE_APPEND);

// Criar um arquivo .htaccess temporário com configurações CORS
$htaccessContent = <<<EOT
# Configurações CORS
<IfModule mod_headers.c>
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
    Header always set Access-Control-Max-Age "3600"
</IfModule>

# Tratar requisições OPTIONS
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_METHOD} OPTIONS
    RewriteRule ^(.*)$ $1 [R=200,L]
</IfModule>
EOT;

// Salvar o arquivo .htaccess
file_put_contents(__DIR__ . '/.htaccess', $htaccessContent);
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Arquivo .htaccess criado\n", FILE_APPEND);

// Criar um arquivo index.php com cabeçalhos CORS
$indexContent = <<<EOT
<?php
// Adicionar cabeçalhos CORS a todas as requisições
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");

// Responder imediatamente às solicitações OPTIONS
if (\$_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Redirecionar para o arquivo solicitado
\$requestUri = \$_SERVER['REQUEST_URI'];
\$file = __DIR__ . \$requestUri;

if (file_exists(\$file) && is_file(\$file)) {
    include \$file;
    exit;
}

// Arquivo não encontrado
http_response_code(404);
echo json_encode(['error' => 'Arquivo não encontrado']);
EOT;

// Salvar o arquivo index.php
file_put_contents(__DIR__ . '/router.php', $indexContent);
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Arquivo router.php criado\n", FILE_APPEND);

// Mensagem para o usuário
echo "Iniciando servidor PHP com suporte a CORS na porta {$port}...\n";
echo "Acesse: http://localhost:{$port}\n";
echo "Pressione Ctrl+C para parar o servidor.\n";

// Comando para iniciar o servidor
$command = "php -S {$host}:{$port} -t {$docRoot} {$docRoot}/router.php";
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Executando comando: {$command}\n", FILE_APPEND);

// Executar o servidor
passthru($command);
