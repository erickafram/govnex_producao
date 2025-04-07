<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

try {
    // Obter conexão com o banco de dados
    $db = getDbConnection();

    // Gerar token único
    $token = bin2hex(random_bytes(32));

    // Inserir token no banco
    $stmt = $db->prepare("INSERT INTO api_tokens (token) VALUES (:token)");
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    echo json_encode(["token" => $token]);
} catch (PDOException $e) {
    error_log("Erro ao gerar token: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Erro ao gerar token"]);
} 