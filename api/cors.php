<?php
/**
 * Arquivo para configuração global de CORS
 * Inclua este arquivo no início de todos os endpoints da API
 */

// Permitir requisições de qualquer origem
header("Access-Control-Allow-Origin: *");

// Métodos HTTP permitidos
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Cabeçalhos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Tempo de cache para preflight
header("Access-Control-Max-Age: 3600");

// Permitir credenciais
header("Access-Control-Allow-Credentials: true");

// Responder imediatamente às solicitações OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
