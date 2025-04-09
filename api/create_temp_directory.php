<?php
// Definir headers para CORS e tipo de conteúdo
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Caminho para diretório temp
$tempDir = __DIR__ . '/../temp';

// Resultado da operação
$result = [
    'success' => false,
    'message' => '',
    'created' => false,
    'permissions_set' => false,
    'temp_directory' => [
        'path' => $tempDir,
        'exists' => false,
        'is_dir' => false,
        'is_writable' => false,
        'permissions' => 'N/A'
    ]
];

try {
    // Verificar se o diretório existe
    if (file_exists($tempDir)) {
        $result['temp_directory']['exists'] = true;
        $result['temp_directory']['is_dir'] = is_dir($tempDir);
        $result['message'] = "O diretório temp já existe.";
        
        if (!$result['temp_directory']['is_dir']) {
            $result['message'] = "O caminho existe, mas não é um diretório. Remova o arquivo e tente novamente.";
        }
    } else {
        // Criar diretório
        if (mkdir($tempDir, 0777, true)) {
            $result['created'] = true;
            $result['temp_directory']['exists'] = true;
            $result['temp_directory']['is_dir'] = true;
            $result['message'] = "Diretório temp criado com sucesso.";
        } else {
            $result['message'] = "Não foi possível criar o diretório temp.";
        }
    }
    
    // Verificar e ajustar permissões se o diretório existir
    if ($result['temp_directory']['exists'] && $result['temp_directory']['is_dir']) {
        // Definir permissões
        if (chmod($tempDir, 0777)) {
            $result['permissions_set'] = true;
            $result['message'] .= " Permissões definidas como 777.";
        } else {
            $result['message'] .= " Não foi possível definir as permissões.";
        }
        
        // Atualizar informações de permissões
        $result['temp_directory']['permissions'] = substr(sprintf('%o', fileperms($tempDir)), -4);
        $result['temp_directory']['is_writable'] = is_writable($tempDir);
    }
    
    // Verificar se a operação foi bem-sucedida
    $result['success'] = ($result['temp_directory']['exists'] && 
                         $result['temp_directory']['is_dir'] && 
                         $result['temp_directory']['is_writable']);
    
    // Criar um arquivo de teste no diretório, se existir
    if ($result['success']) {
        $testFileName = $tempDir . '/test_' . time() . '.txt';
        $testContent = "Teste de escrita no diretório temp: " . date('Y-m-d H:i:s');
        
        if (file_put_contents($testFileName, $testContent)) {
            $result['message'] .= " Arquivo de teste criado com sucesso.";
        } else {
            $result['message'] .= " Não foi possível criar arquivo de teste.";
            $result['success'] = false;
        }
    }
    
    // Informações do servidor para diagnóstico
    $result['server_info'] = [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
        'user' => function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'Unknown',
        'process_owner' => get_current_user()
    ];
    
    // Recomendações com base no resultado
    if (!$result['success']) {
        $result['recommendations'] = [
            "Verifique se o usuário do servidor web tem permissão para criar diretórios.",
            "Execute 'chmod 777 -R /var/www/html/govnex_producao/temp' no servidor.",
            "Crie manualmente o diretório e dê permissões completas: 'mkdir -p /var/www/html/govnex_producao/temp && chmod 777 /var/www/html/govnex_producao/temp'."
        ];
    }
    
} catch (Exception $e) {
    $result['success'] = false;
    $result['message'] = "Erro: " . $e->getMessage();
}

// Retornar resultado como JSON
echo json_encode($result, JSON_PRETTY_PRINT); 