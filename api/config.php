<?php
// Carregar variáveis de ambiente do arquivo .env
function loadEnv()
{
    // Detectar ambiente
    $isProduction = (
        isset($_SERVER['SERVER_NAME']) && 
        (strpos($_SERVER['SERVER_NAME'], '161.35.60.249') !== false || 
         strpos($_SERVER['SERVER_NAME'], 'govnex.site') !== false)
    );
    
    // Selecionar o arquivo .env apropriado
    $envFile = $isProduction 
        ? __DIR__ . '/../.env.production' 
        : __DIR__ . '/../.env';
    
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

                $_ENV[$name] = $value;
                putenv("{$name}={$value}");
            }
        }
    }
    
    // Log do ambiente carregado
    $logFile = __DIR__ . '/config_log.txt';
    file_put_contents(
        $logFile, 
        date('Y-m-d H:i:s') . " - Ambiente: " . ($isProduction ? "Produção" : "Desenvolvimento") . 
        ", Arquivo: " . $envFile . "\n", 
        FILE_APPEND
    );
}

// Carregar variáveis de ambiente
loadEnv();

// Configuração do banco de dados
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: '';
$dbName = getenv('DB_NAME') ?: 'govnex';
$dbPort = getenv('DB_PORT') ?: '3306';

// Função para obter conexão com o banco de dados
function getDbConnection()
{
    global $dbHost, $dbUser, $dbPassword, $dbName, $dbPort;

    try {
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, $dbUser, $dbPassword, $options);
    } catch (PDOException $e) {
        // Em produção, você deve registrar o erro e não exibi-lo
        die("Erro de conexão com o banco de dados: " . $e->getMessage());
    }
}

// Função para responder com JSON
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Função para obter dados da requisição
function getRequestData()
{
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if (stripos($contentType, 'application/json') !== false) {
        // Receber dados JSON
        $content = file_get_contents('php://input');
        $data = json_decode($content, true);
    } else {
        // Receber dados de formulário
        $data = $_POST;
    }

    return $data;
}

// Função para habilitar CORS
function enableCors()
{
    // Permitir acesso de qualquer origem durante o desenvolvimento
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    // Responder imediatamente às solicitações OPTIONS (preflight)
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// Habilitar CORS para todas as requisições
enableCors();
