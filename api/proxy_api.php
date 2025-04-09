<?php
// Configuração de cabeçalhos e CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Origin, X-Domain");
header("Content-Type: application/json; charset=UTF-8");

// Log de requisições
error_log("[" . date('Y-m-d H:i:s') . "] Requisição recebida em proxy_api.php: " . json_encode($_REQUEST));

// Se for uma requisição OPTIONS, retornar apenas os cabeçalhos
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir o arquivo de configuração do banco de dados
require_once __DIR__ . '/config/database.php';

// Verificar token de autenticação
if (!isset($_GET['token']) && !isset($_POST['token'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Token de autenticação necessário"]);
    exit;
}

$token = isset($_GET['token']) ? $_GET['token'] : $_POST['token'];

// Obter o domínio de origem
$dominio = 'Desconhecido';
if (!empty($_SERVER['HTTP_ORIGIN'])) {
    $dominio = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
    $dominio = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
} elseif (!empty($_SERVER['HTTP_X_DOMAIN'])) {
    $dominio = $_SERVER['HTTP_X_DOMAIN'];
} elseif (isset($_GET['domain'])) {
    $dominio = $_GET['domain'];
} elseif (isset($_POST['domain'])) {
    $dominio = $_POST['domain'];
} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
    $dominio = $_SERVER['REMOTE_ADDR'];
}

// Log do domínio detectado
error_log("[" . date('Y-m-d H:i:s') . "] Domínio detectado: " . $dominio);

try {
    // Criar conexão com o banco de dados
    $database = new Database();
    $conn = $database->getConnection();
    
    // Validar token
    $stmt = $conn->prepare("SELECT * FROM api_tokens WHERE token = :token AND is_active = TRUE");
    
    // Verificar se expires_at existe e se está válido
    $stmt = $conn->prepare("
        SELECT * FROM api_tokens 
        WHERE token = :token 
        AND is_active = TRUE
        AND (expires_at IS NULL OR expires_at > NOW())
    ");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Token inválido ou expirado"]);
        exit;
    }
    
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se o token tem user_id associado, usar este usuário
    $usuarioIdFromToken = null;
    if (isset($tokenData['user_id']) && $tokenData['user_id'] !== null) {
        $usuarioIdFromToken = $tokenData['user_id'];
    }
    
    // Verificar se foi fornecido um CNPJ ou CPF
    $documento = null;
    $tipoDocumento = null;
    
    if (isset($_GET['cnpj']) || isset($_POST['cnpj'])) {
        $documento = isset($_GET['cnpj']) ? $_GET['cnpj'] : $_POST['cnpj'];
        $tipoDocumento = 'cnpj';
    } elseif (isset($_GET['cpf']) || isset($_POST['cpf'])) {
        $documento = isset($_GET['cpf']) ? $_GET['cpf'] : $_POST['cpf'];
        $tipoDocumento = 'cpf';
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "CNPJ ou CPF não fornecido"]);
        exit;
    }
    
    // Remover caracteres não numéricos
    $documento = preg_replace('/[^0-9]/', '', $documento);
    
    // Validar formato do documento
    if ($tipoDocumento === 'cnpj' && strlen($documento) !== 14) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "CNPJ inválido"]);
        exit;
    } elseif ($tipoDocumento === 'cpf' && strlen($documento) !== 11) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "CPF inválido"]);
        exit;
    }
    
    // Obter usuário vinculado ao domínio
    $stmt = $conn->prepare("SELECT id, nome, credito FROM usuarios WHERE dominio = :dominio");
    $stmt->bindParam(':dominio', $dominio);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se não encontrou usuário pelo domínio, tentar pelo token
    if (!$usuario && $usuarioIdFromToken) {
        $stmt = $conn->prepare("SELECT id, nome as nome, credito FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $usuarioIdFromToken);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$usuario) {
        http_response_code(400);
        echo json_encode([
            "success" => false, 
            "error" => "Domínio não registrado: $dominio. Por favor, verifique se o domínio está corretamente configurado em seu perfil."
        ]);
        exit;
    }
    
    // Definir custo da consulta
    $custo = 0.12; // Custo padrão para consulta de CNPJ
    if ($tipoDocumento === 'cpf') {
        $custo = 0.15; // Custo maior para consulta de CPF
    }
    
    // Verificar se o usuário tem créditos suficientes
    if ($usuario['credito'] < $custo) {
        http_response_code(402);
        echo json_encode([
            "success" => false, 
            "error" => "Créditos insuficientes para realizar a consulta",
            "saldo" => $usuario['credito'],
            "custo" => $custo,
            "necessario" => $custo - $usuario['credito']
        ]);
        exit;
    }
    
    // Iniciar transação
    $conn->beginTransaction();
    
    try {
        // Registrar a consulta
        $stmt = $conn->prepare(
            "INSERT INTO consultas_log (cnpj_consultado, dominio_origem, custo, usuario_id) 
             VALUES (:documento, :dominio, :custo, :usuario_id)"
        );
        $stmt->bindParam(':documento', $documento);
        $stmt->bindParam(':dominio', $dominio);
        $stmt->bindParam(':custo', $custo);
        $stmt->bindParam(':usuario_id', $usuario['id']);
        $stmt->execute();
        
        // Atualizar créditos do usuário
        $stmt = $conn->prepare(
            "UPDATE usuarios SET credito = credito - :custo 
             WHERE id = :usuario_id AND credito >= :custo"
        );
        $stmt->bindParam(':custo', $custo);
        $stmt->bindParam(':usuario_id', $usuario['id']);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Falha ao atualizar créditos");
        }
        
        // Commit da transação
        $conn->commit();
        
        // Log de sucesso
        error_log("[" . date('Y-m-d H:i:s') . "] Consulta registrada com sucesso para o usuário {$usuario['id']} ({$usuario['nome']}). Crédito restante: " . ($usuario['credito'] - $custo));
        
        // Atualizar último uso do token
        try {
            $stmt = $conn->prepare("UPDATE api_tokens SET last_used = NOW() WHERE token = :token");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
        } catch (Exception $e) {
            // Ignorar erros aqui, apenas para log
            error_log("[" . date('Y-m-d H:i:s') . "] Aviso: Não foi possível atualizar último uso do token: " . $e->getMessage());
        }
        
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollBack();
        error_log("[" . date('Y-m-d H:i:s') . "] Erro na transação: " . $e->getMessage());
        
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Falha ao processar consulta: " . $e->getMessage()]);
        exit;
    }
    
    // Consultar API externa
    $apiUrl = "";
    
    if ($tipoDocumento === 'cnpj') {
        $apiUrl = "http://161.35.60.249:8000/{$documento}";
    } else {
        $apiUrl = "http://161.35.60.249:8000/cpf/{$documento}";
    }
    
    // Configurar e executar requisição CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        error_log("[" . date('Y-m-d H:i:s') . "] Erro CURL: " . curl_error($ch));
        http_response_code(500);
        echo json_encode([
            "success" => false, 
            "error" => "Erro ao acessar a API: " . curl_error($ch),
            "credito_restante" => $usuario['credito'] - $custo
        ]);
        curl_close($ch);
        exit;
    }
    
    curl_close($ch);
    
    // Verificar se a resposta é válida
    $responseData = json_decode($response, true);
    
    if ($httpCode !== 200 || !$responseData) {
        error_log("[" . date('Y-m-d H:i:s') . "] Resposta inválida da API. HTTP Code: $httpCode, Resposta: " . substr($response, 0, 500));
        
        // Se a API externa retornou erro, retornar um formato padrão
        echo json_encode([
            "success" => false,
            "error" => "A consulta à API externa falhou",
            "api_status_code" => $httpCode,
            "credito_restante" => $usuario['credito'] - $custo
        ]);
        exit;
    }
    
    // Adicionar informações sobre o crédito restante à resposta
    if (is_array($responseData)) {
        $responseData['credito_restante'] = $usuario['credito'] - $custo;
        $responseData['success'] = true;
        $response = json_encode($responseData);
    } else {
        // Se a resposta não for um array, criar um wrapper
        $response = json_encode([
            "success" => true,
            "data" => $responseData,
            "credito_restante" => $usuario['credito'] - $custo
        ]);
    }
    
    // Retornar a resposta
    echo $response;
    
} catch (PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Erro PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => "Erro no banco de dados: " . $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Erro geral: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => "Erro ao processar requisição: " . $e->getMessage()
    ]);
    exit;
} 