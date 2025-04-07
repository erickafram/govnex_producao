<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;

class Payment
{
    private $db;
    private $clientUri = 'https://api.digitopayoficial.com.br/';
    private $clientId = '41b9547d-1053-47ee-8b57-322ca8fd67b1';
    private $clientSecret = '1697c51a-7b58-4370-b5dd-f54183169523';
    private $logFile = __DIR__ . '/../../logs/digitopay_pagamentos.log';

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function createPayment($userId, $amount, $userData)
    {
        try {
            // Verificar se o usuário existe
            $stmt = $this->db->prepare("SELECT id, nome, email FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ];
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($amount < 400.00) {
                return [
                    'success' => false,
                    'message' => 'Valor mínimo de R$ 400,00 para recarga'
                ];
            }

            try {
                $token = $this->getAuthToken();
                if (!$token) {
                    return [
                        'success' => false,
                        'message' => 'Falha na autenticação com a API de pagamentos'
                    ];
                }
            } catch (Exception $e) {
                $this->logError("Erro na autenticação: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Falha na autenticação com a Digitopay: ' . $e->getMessage()
                ];
            }

            // Validar e formatar CPF
            $cpf = preg_replace('/[^0-9]/', '', $userData['cpf']);

            if (strlen($cpf) !== 11 || !$this->validateCPF($cpf)) {
                $this->logError("CPF inválido fornecido: " . $userData['cpf']);
                return [
                    'success' => false,
                    'message' => 'CPF inválido. Por favor, verifique o número informado.'
                ];
            }

            $this->logError("CPF formatado para envio: $cpf"); // Log para debug

            $paymentData = [
                "dueDate" => date('Y-m-d\TH:i:s', strtotime('+1 day')),
                "paymentOptions" => ["PIX"],
                "person" => [
                    "cpf" => $cpf,
                    "name" => $userData['name']
                ],
                "value" => $amount,
                "callbackUrl" => "http://localhost/react/govnex/novogovnex/pix-credit-nexus/api/webhook_payments.php"
            ];

            try {
                $paymentResponse = $this->callDigitopayAPI(
                    $this->clientUri . 'api/deposit',
                    $token,
                    $paymentData
                );
            } catch (Exception $e) {
                $this->logError("Erro ao criar pagamento: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Erro ao criar pagamento: ' . $e->getMessage()
                ];
            }

            // Gerar QR Code
            $qrCode = new QrCode($paymentResponse['pixCopiaECola']);
            $writer = new PngWriter();
            $qrImage = $writer->write($qrCode);

            // Salvar QR Code temporariamente
            $tempDir = __DIR__ . '/../../temp';
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $qrPath = $tempDir . '/qrcode_' . $paymentResponse['id'] . '.png';
            file_put_contents($qrPath, $qrImage->getString());
            
            // Registrar log para depuração
            $this->logError("QR Code salvo em: " . $qrPath);

            // Registrar a transação no banco de dados
            try {
                $this->registerPaymentInDatabase(
                    $userId,
                    $amount,
                    $paymentResponse['id']
                );
            } catch (Exception $e) {
                $this->logError("Erro ao registrar pagamento: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Erro ao registrar pagamento: ' . $e->getMessage()
                ];
            }

            return [
                'success' => true,
                'qr_code_url' => '/react/govnex/novogovnex/pix-credit-nexus/temp/qrcode_' . $paymentResponse['id'] . '.png',
                'transaction_id' => $paymentResponse['id'],
                'pix_code' => $paymentResponse['pixCopiaECola']
            ];
        } catch (Exception $e) {
            $this->logError("Erro ao criar pagamento: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao criar pagamento: ' . $e->getMessage()
            ];
        }
    }

    private function getAuthToken()
    {
        try {
            $this->logError("Iniciando autenticação com a Digitopay");
            $this->logError("URL: " . $this->clientUri . 'api/token/api');
            $this->logError("ClientID: " . substr($this->clientId, 0, 8) . "...");
            
            $ch = curl_init($this->clientUri . 'api/token/api');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'clientId' => $this->clientId,
                    'secret' => $this->clientSecret
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_SSL_VERIFYPEER => false,  // Desativar verificação SSL para testes
                CURLOPT_TIMEOUT => 30,
                CURLOPT_VERBOSE => true
            ]);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);

            $this->logError("Resposta curl: " . ($error ? "ERRO: " . $error : "OK"));
            $this->logError("HTTP Status: " . ($info['http_code'] ?? 'N/A'));
            
            // Registrar a resposta completa para depuração
            $this->logError("Resposta completa: " . $response);

            if (!$response) {
                $this->logError("Falha na autenticação: " . $error);
                throw new Exception("Falha na autenticação com a Digitopay: " . $error);
            }

            $tokenData = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError("Erro ao decodificar JSON: " . json_last_error_msg());
                $this->logError("Resposta recebida: " . substr($response, 0, 500));
                throw new Exception("Resposta inválida da Digitopay: " . json_last_error_msg());
            }
            
            if (!isset($tokenData['accessToken'])) {
                $this->logError("Token não encontrado na resposta: " . json_encode($tokenData));
                throw new Exception("Token não encontrado na resposta da Digitopay");
            }
            
            $this->logError("Token obtido com sucesso");
            return $tokenData['accessToken'];
        } catch (Exception $e) {
            $this->logError("Exceção na obtenção do token: " . $e->getMessage());
            throw new Exception("Falha na autenticação com a Digitopay: " . $e->getMessage());
        }
    }

    private function callDigitopayAPI($url, $token, $data)
    {
        try {
            $this->logError("Chamando API Digitopay: " . $url);
            $this->logError("Dados: " . json_encode($data));
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token
                ],
                CURLOPT_SSL_VERIFYPEER => false, // Desativar verificação SSL para testes
                CURLOPT_TIMEOUT => 30,
                CURLOPT_VERBOSE => true
            ]);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            
            $this->logError("Resposta curl: " . ($error ? "ERRO: " . $error : "OK"));
            $this->logError("HTTP Status: " . ($info['http_code'] ?? 'N/A'));
            
            if (!$response) {
                $this->logError("Erro na chamada à API: " . $error);
                throw new Exception("Erro na comunicação com a Digitopay: " . $error);
            }
            
            // Registrar a resposta completa para depuração
            $this->logError("Resposta completa: " . $response);
            
            $responseData = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError("Erro ao decodificar JSON: " . json_last_error_msg());
                $this->logError("Resposta recebida: " . substr($response, 0, 500));
                throw new Exception("Resposta inválida da Digitopay: " . json_last_error_msg());
            }
            
            if (isset($responseData['error']) || isset($responseData['errors'])) {
                $errorMsg = isset($responseData['error']) ? $responseData['error'] : json_encode($responseData['errors']);
                $this->logError("Erro retornado pela API: " . $errorMsg);
                throw new Exception("Erro na API Digitopay: " . $errorMsg);
            }
            
            // Verificar se a resposta contém os campos esperados
            if (!isset($responseData['pixCopiaECola']) || !isset($responseData['id'])) {
                $this->logError("Resposta incompleta da API: " . json_encode($responseData));
                throw new Exception("Resposta incompleta da Digitopay. Faltam campos obrigatórios.");
            }
            
            $this->logError("Chamada à API bem-sucedida");
            return $responseData;
        } catch (Exception $e) {
            $this->logError("Exceção na chamada à API: " . $e->getMessage());
            throw $e;
        }
    }

    private function registerPaymentInDatabase($userId, $amount, $transactionId)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO pagamentos 
                 (usuario_id, valor, status, codigo_transacao, data_criacao, data_atualizacao) 
                 VALUES (:usuario_id, :valor, 'pendente', :codigo_transacao, NOW(), NOW())"
            );
            $stmt->bindParam(':usuario_id', $userId);
            $stmt->bindParam(':valor', $amount);
            $stmt->bindParam(':codigo_transacao', $transactionId);
            $stmt->execute();
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Erro ao registrar pagamento: " . $e->getMessage());
        }
    }

    public function getPaymentStatus($transactionId)
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT status FROM pagamentos WHERE codigo_transacao = :codigo_transacao"
            );
            $stmt->bindParam(':codigo_transacao', $transactionId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Pagamento não encontrado'
                ];
            }
            
            return [
                'success' => true,
                'status' => $result['status'],
                'paid' => ($result['status'] === 'pago')
            ];
        } catch (Exception $e) {
            $this->logError("Erro ao verificar status do pagamento: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao verificar status do pagamento'
            ];
        }
    }

    private function validateCPF($cpf)
    {
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validação do dígito verificador
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    private function logError($message)
    {
        try {
            // Verificar se o diretório de logs existe
            $logDir = dirname($this->logFile);
            if (!is_dir($logDir)) {
                if (!mkdir($logDir, 0755, true)) {
                    error_log("Não foi possível criar o diretório de logs: " . $logDir);
                    return;
                }
            }
            
            // Verificar se o arquivo de log existe ou pode ser criado
            if (!file_exists($this->logFile)) {
                if (!touch($this->logFile)) {
                    error_log("Não foi possível criar o arquivo de log: " . $this->logFile);
                    return;
                }
                chmod($this->logFile, 0644);
            }
            
            // Verificar se o arquivo é gravável
            if (!is_writable($this->logFile)) {
                error_log("Arquivo de log não é gravável: " . $this->logFile);
                return;
            }
            
            // Gravar a mensagem no arquivo de log
            $logMessage = "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;
            if (file_put_contents($this->logFile, $logMessage, FILE_APPEND) === false) {
                error_log("Falha ao gravar no arquivo de log: " . $this->logFile);
            }
        } catch (Exception $e) {
            // Em caso de erro, registrar no log do PHP
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }
}
