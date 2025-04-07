<?php
// Script para testar o endpoint direct-login.php

// URL do endpoint
$url = 'http://localhost:8081/api/direct-login.php';

// Dados de login para teste
$data = [
    'email' => 'infovisa.gurupi@govnex.site',
    'password' => '123456'
];

// Configurar a requisição cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// Executar a requisição
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Exibir resultados
echo "Código de status: " . $httpCode . "\n";
echo "Resposta: " . $response . "\n";

// Salvar resultado em um arquivo de log
file_put_contents(__DIR__ . '/test_login_result.txt', 
    date('Y-m-d H:i:s') . " - Status: " . $httpCode . "\nResposta: " . $response . "\n", 
    FILE_APPEND);
