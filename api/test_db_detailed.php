<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se o arquivo .env existe
$envFile = __DIR__ . '/../.env';
$envProdFile = __DIR__ . '/../.env.production';

echo "Verificando arquivos .env:\n";
echo ".env existe: " . (file_exists($envFile) ? "SIM" : "NÃO") . "\n";
echo ".env.production existe: " . (file_exists($envProdFile) ? "SIM" : "NÃO") . "\n";

if (file_exists($envFile)) {
    echo "Conteúdo do arquivo .env:\n";
    echo file_get_contents($envFile) . "\n";
}

// Primeiro teste: usando PDO diretamente com o usuário root
echo "\nTeste direto com PDO (root):\n";
try {
    $dsn = "mysql:host=localhost;port=3306;dbname=govnex;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    $stmt = $pdo->query('SELECT NOW() as server_time');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Conexão direta com usuário 'root' estabelecida com sucesso\n";
    echo "Server time: " . $result['server_time'] . "\n";
} catch (PDOException $e) {
    echo "Falha na conexão direta com 'root': " . $e->getMessage() . "\n";
}

require_once __DIR__ . '/config/database.php';

echo "\nTestando a classe Database diretamente:\n";
$database = new Database();
// Usando reflection para inspecionar as propriedades da classe (mesmo que sejam privadas)
$reflect = new ReflectionClass($database);
foreach (['host', 'db_name', 'username', 'password', 'port', 'isProduction'] as $property) {
    $prop = $reflect->getProperty($property);
    $prop->setAccessible(true);
    echo "Database->$property: " . $prop->getValue($database) . "\n";
}

function testMySQLConnection() {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Testar uma query simples
        $stmt = $conn->query('SELECT NOW() as server_time');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Conexão com o banco de dados estabelecida com sucesso\n";
        echo "Server time: " . $result['server_time'] . "\n";
        echo "PHP Version: " . PHP_VERSION . "\n";
        echo "PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "\n";
        echo "MySQL Version: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
        echo "Connection Status: " . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
        return true;
    } catch (Exception $e) {
        echo "Falha na conexão com o banco de dados\n";
        echo "Error details: " . $e->getMessage() . "\n";
        echo "PHP Version: " . PHP_VERSION . "\n";
        echo "PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "\n";
        return false;
    }
}

echo "\nTestando conexão com o banco de dados usando Database:\n";
testMySQLConnection();

// Testar também o método getDbConnection de db_config.php
echo "\nTestando conexão usando db_config.php (função getDbConnection):\n";
require_once __DIR__ . '/db_config.php';
try {
    $conn = getDbConnection();
    $stmt = $conn->query('SELECT NOW() as server_time');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Conexão usando getDbConnection() estabelecida com sucesso\n";
    echo "Server time: " . $result['server_time'] . "\n";
    
    // Verificar as configurações
    $config = getDbConfig();
    echo "Configurações obtidas via getDbConfig():\n";
    foreach ($config as $key => $value) {
        echo "$key: $value\n";
    }
} catch (Exception $e) {
    echo "Falha na conexão usando getDbConnection(): " . $e->getMessage() . "\n";
}