<?php
// Este arquivo fornece um endpoint para transações,
// tenta buscar dados reais e usa dados de exemplo como fallback

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/middleware/auth.php';

// Configurar cabeçalhos para JSON e CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Registrar logs para debug
error_log("API mock_transactions.php iniciada");

// Se for uma requisição OPTIONS (preflight), apenas retornar OK
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Função para obter transações reais do banco de dados
function getRealTransactions($userId, $limit = 0) {
    global $db;
    
    try {
        // Verificar se a tabela pagamentos existe
        $tableExists = $db->query("SHOW TABLES LIKE 'pagamentos'")->rowCount() > 0;
        
        if (!$tableExists) {
            error_log("Tabela 'pagamentos' não encontrada no banco de dados");
            return [];
        }
        
        // Consultar pagamentos do usuário
        $sql = "
            SELECT 
                id, 
                usuario_id, 
                valor as amount, 
                status,
                codigo_transacao as transactionCode,
                data_criacao as createdAt,
                data_atualizacao as updatedAt
            FROM pagamentos
            WHERE usuario_id = :userId
            ORDER BY data_criacao DESC
        ";
        
        // Adicionar limite se necessário
        if ($limit > 0) {
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        
        if ($limit > 0) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Converter para o formato esperado pelo frontend
        if (!empty($transactions)) {
            foreach ($transactions as &$transaction) {
                $transaction['id'] = (string)$transaction['id'];
                $transaction['userId'] = (string)$transaction['usuario_id'];
                unset($transaction['usuario_id']);
                $transaction['amount'] = (float)$transaction['amount'];
                $transaction['type'] = 'deposit'; // Todos os pagamentos são considerados depósitos
                $transaction['description'] = 'Recarga via PIX';
            }
            
            error_log("Encontradas " . count($transactions) . " transações reais para o usuário $userId");
            return $transactions;
        }
        
        error_log("Nenhuma transação real encontrada para o usuário $userId");
        return [];
    } catch (Exception $e) {
        error_log("Erro ao buscar transações reais: " . $e->getMessage());
        return [];
    }
}

// Mock data for transactions
function getMockTransactions($userId, $limit = 0) {
    $transactions = [
        [
            'id' => '1',
            'userId' => (string)$userId,
            'amount' => 200.00,
            'status' => 'completed',
            'transactionCode' => '3f8e4c72-5272-489e-be80-eb03b000463a',
            'createdAt' => date('Y-m-d H:i:s', strtotime('-3 days')),
            'updatedAt' => date('Y-m-d H:i:s', strtotime('-3 days')),
            'type' => 'deposit',
            'description' => 'Recarga via PIX'
        ],
        [
            'id' => '2',
            'userId' => (string)$userId,
            'amount' => 100.00,
            'status' => 'completed',
            'transactionCode' => '5e9a1c36-8742-4d98-b3a1-f25c87b612e9',
            'createdAt' => date('Y-m-d H:i:s', strtotime('-7 days')),
            'updatedAt' => date('Y-m-d H:i:s', strtotime('-7 days')),
            'type' => 'deposit',
            'description' => 'Recarga via PIX'
        ],
        [
            'id' => '3',
            'userId' => (string)$userId,
            'amount' => 25.00,
            'status' => 'completed',
            'transactionCode' => '7d8e1a52-963c-4b78-a5f2-c9e74d3b89f1',
            'createdAt' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'updatedAt' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'type' => 'withdrawal',
            'description' => 'Consulta de CNPJ'
        ],
        [
            'id' => '4',
            'userId' => (string)$userId,
            'amount' => 50.00,
            'status' => 'pending',
            'transactionCode' => '9e2c1b38-4a56-7f81-d3c9-a6b2e1f53d7c',
            'createdAt' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'updatedAt' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'type' => 'deposit',
            'description' => 'Recarga pendente'
        ],
        [
            'id' => '5',
            'userId' => (string)$userId,
            'amount' => 10.00,
            'status' => 'completed',
            'transactionCode' => '1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p',
            'createdAt' => date('Y-m-d H:i:s', strtotime('-15 days')),
            'updatedAt' => date('Y-m-d H:i:s', strtotime('-15 days')),
            'type' => 'withdrawal',
            'description' => 'Consulta de CNPJ'
        ]
    ];
    
    // Aplicar limite se necessário
    if ($limit > 0 && $limit < count($transactions)) {
        $transactions = array_slice($transactions, 0, $limit);
    }
    
    return $transactions;
}

try {
    error_log("mock_transactions.php: Iniciando processamento da requisição");

    // Obter a conexão com o banco de dados
    $db = getDbConnection();
    error_log("mock_transactions.php: Conexão com banco de dados estabelecida");
    
    // Obter o ID do usuário autenticado
    error_log("mock_transactions.php: Verificando autenticação...");
    $userId = checkAuth();
    error_log("mock_transactions.php: Resultado da autenticação: " . ($userId ? "Usuário ID: $userId" : "Falha na autenticação"));
    
    if (!$userId) {
        error_log("mock_transactions.php: Autenticação falhou - retornando erro 401");
        http_response_code(401);
        echo json_encode([
            "success" => false, 
            "error" => "Não autorizado", 
            "message" => "Credenciais inválidas ou expiradas."
        ]);
        exit;
    }
    
    // Processar a requisição
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
    error_log("mock_transactions.php: Limite definido: " . ($limit > 0 ? $limit : "sem limite"));
    
    // Tentar obter transações reais
    error_log("mock_transactions.php: Buscando transações reais...");
    $transactions = getRealTransactions($userId, $limit);
    
    // Se não houver transações reais, usar dados fictícios
    if (empty($transactions)) {
        error_log("mock_transactions.php: Usando dados fictícios para o usuário $userId");
        $transactions = getMockTransactions($userId, $limit);
        error_log("mock_transactions.php: Gerados " . count($transactions) . " registros fictícios");
    } else {
        error_log("mock_transactions.php: Encontradas " . count($transactions) . " transações reais");
    }
    
    // Adicionar alguns registros de consultas como transações do tipo "withdrawal"
    try {
        error_log("mock_transactions.php: Tentando adicionar registros de consultas...");
        // Verificar se a tabela consultas_log existe
        $logTableExists = $db->query("SHOW TABLES LIKE 'consultas_log'")->rowCount() > 0;
        
        if ($logTableExists) {
            error_log("mock_transactions.php: Tabela consultas_log encontrada");
            // Buscar o domínio do usuário
            $stmt = $db->prepare("SELECT dominio FROM usuarios WHERE id = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $userDomain = $stmt->fetchColumn();
            
            if ($userDomain) {
                error_log("mock_transactions.php: Domínio do usuário encontrado: $userDomain");
                // Buscar as consultas feitas com este domínio
                $sql = "
                    SELECT 
                        id,
                        cnpj_consultado,
                        data_consulta as createdAt,
                        custo as amount
                    FROM consultas_log
                    WHERE dominio_origem = :dominio
                    ORDER BY data_consulta DESC
                ";
                
                if ($limit > 0) {
                    $sql .= " LIMIT :limit";
                }
                
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':dominio', $userDomain);
                
                if ($limit > 0) {
                    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                }
                
                $stmt->execute();
                $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("mock_transactions.php: Encontradas " . count($consultas) . " consultas para o domínio $userDomain");
                
                // Converter consultas para o formato de transações
                foreach ($consultas as $consulta) {
                    $withdrawalTransaction = [
                        'id' => 'c' . $consulta['id'], // prefixo 'c' para diferenciar
                        'userId' => (string)$userId,
                        'amount' => (float)$consulta['amount'],
                        'status' => 'completed',
                        'transactionCode' => md5($consulta['id'] . $consulta['cnpj_consultado']),
                        'createdAt' => $consulta['createdAt'],
                        'updatedAt' => $consulta['createdAt'],
                        'type' => 'withdrawal',
                        'description' => 'Consulta CNPJ: ' . substr($consulta['cnpj_consultado'], 0, 2) . '.' . 
                                         substr($consulta['cnpj_consultado'], 2, 3) . '.' . 
                                         substr($consulta['cnpj_consultado'], 5, 3) . '/' . 
                                         substr($consulta['cnpj_consultado'], 8, 4) . '-' . 
                                         substr($consulta['cnpj_consultado'], 12, 2)
                    ];
                    
                    $transactions[] = $withdrawalTransaction;
                }
                
                // Ordenar todas as transações por data (mais recentes primeiro)
                usort($transactions, function($a, $b) {
                    return strtotime($b['createdAt']) - strtotime($a['createdAt']);
                });
                
                // Reaplicar o limite se necessário
                if ($limit > 0 && count($transactions) > $limit) {
                    $transactions = array_slice($transactions, 0, $limit);
                }
                
                error_log("mock_transactions.php: Total após adicionar consultas: " . count($transactions));
            } else {
                error_log("mock_transactions.php: Domínio do usuário não encontrado");
            }
        } else {
            error_log("mock_transactions.php: Tabela consultas_log não encontrada");
        }
    } catch (Exception $e) {
        error_log("mock_transactions.php: Erro ao buscar consultas: " . $e->getMessage());
    }
    
    error_log("mock_transactions.php: Enviando resposta com " . count($transactions) . " transações");
    echo json_encode([
        "success" => true,
        "transactions" => $transactions,
        "userId" => $userId,
        "count" => count($transactions),
        "limit" => $limit > 0 ? $limit : "sem limite",
        "isReal" => !empty(getRealTransactions($userId, 1))
    ]);
    
} catch (Exception $e) {
    error_log("mock_transactions.php: Erro geral: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Erro interno do servidor",
        "dev_message" => $e->getMessage()
    ]);
} 