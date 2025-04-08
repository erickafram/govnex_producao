<?php
require_once __DIR__ . '/../config.php'; // Adicionado: incluir config.php para funções auxiliares
require_once __DIR__ . '/../models/Consulta.php';
require_once __DIR__ . '/../models/User.php';

class ConsultaController
{
    public $consultaModel;
    private $userModel;

    public function __construct()
    {
        $this->consultaModel = new Consulta();
        $this->userModel = new User();
    }

    /**
     * Obter consultas disponíveis e histórico para um usuário
     * 
     * @param int $userId ID do usuário
     * @return array Resposta com status e número de consultas
     */
    public function getConsultasDisponiveis($userId)
    {
        // Converter para inteiro para garantir que é um número
        $userId = intval($userId);
        error_log("ConsultaController.getConsultasDisponiveis: Iniciando para usuário ID $userId");
        
        if ($userId <= 0) {
            error_log("ConsultaController.getConsultasDisponiveis: ID de usuário inválido");
            jsonResponse(['error' => 'ID de usuário inválido'], 400);
            return;
        }

        try {
            // Obter usuário
            $user = $this->userModel->getById($userId);
            error_log("ConsultaController.getConsultasDisponiveis: Dados do usuário: " . ($user ? json_encode($user) : "não encontrado"));
            
            if (!$user) {
                error_log("ConsultaController.getConsultasDisponiveis: Usuário não encontrado");
                jsonResponse(['error' => 'Usuário não encontrado'], 404);
                return;
            }

            // Verificar se o usuário tem crédito
            $credito = isset($user['balance']) ? floatval($user['balance']) : 0;
            error_log("ConsultaController.getConsultasDisponiveis: Crédito do usuário: $credito");
            
            // Calcular número de consultas disponíveis (cada consulta custa 0.12)
            $consultasDisponiveis = floor($credito / 0.12);
            error_log("ConsultaController.getConsultasDisponiveis: Consultas disponíveis calculadas: $consultasDisponiveis");
            
            // Verificar se o domínio está presente
            $dominio = $user['domain'] ?? null;
            error_log("ConsultaController.getConsultasDisponiveis: Domínio do usuário: " . ($dominio ?: "não definido"));
            
            // Obter histórico de consultas
            $historicoConsultas = [];

            if ($dominio) {
                $historicoConsultas = $this->consultaModel->getByDominio($dominio, 10);
                error_log("ConsultaController.getConsultasDisponiveis: Histórico de consultas: " . json_encode($historicoConsultas));
            }

            // Retornar resposta
            $response = [
                'success' => true,
                'credito' => $credito,
                'consultasDisponiveis' => $consultasDisponiveis,
                'historicoConsultas' => $historicoConsultas
            ];
            
            error_log("ConsultaController.getConsultasDisponiveis: Resposta: " . json_encode($response));
            jsonResponse($response);
        } catch (Exception $e) {
            error_log("Erro ao obter consultas: " . $e->getMessage());
            jsonResponse(['error' => 'Erro ao processar a solicitação: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Registrar uma nova consulta de CNPJ
     */
    public function registrarConsulta()
    {
        // Obter dados da requisição
        $data = getRequestData();

        // Validar dados obrigatórios
        if (empty($data['cnpj']) || empty($data['dominio'])) {
            jsonResponse(['error' => 'CNPJ e domínio são obrigatórios'], 400);
        }

        // Limpar CNPJ (remover caracteres não numéricos)
        $cnpj = preg_replace('/[^0-9]/', '', $data['cnpj']);

        // Validar CNPJ
        if (strlen($cnpj) !== 14) {
            jsonResponse(['error' => 'CNPJ inválido'], 400);
        }

        // Verificar se o domínio existe e tem crédito suficiente
        if (!$this->consultaModel->verificarCredito($data['dominio'])) {
            jsonResponse([
                'error' => 'Crédito insuficiente ou domínio inválido',
                'code' => 'INSUFFICIENT_CREDIT'
            ], 400);
        }

        // Registrar consulta
        $consulta = $this->consultaModel->registrarConsulta($cnpj, $data['dominio']);

        if ($consulta) {
            jsonResponse([
                'success' => true,
                'message' => 'Consulta registrada com sucesso',
                'consulta' => $consulta
            ]);
        } else {
            jsonResponse(['error' => 'Falha ao registrar consulta'], 500);
        }
    }

    /**
     * Listar histórico de consultas para um domínio
     */
    public function listarConsultas()
    {
        // Obter domínio da query string
        $dominio = isset($_GET['dominio']) ? $_GET['dominio'] : null;

        if (!$dominio) {
            jsonResponse(['error' => 'Domínio não informado'], 400);
        }

        // Obter limite da query string (opcional)
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;

        try {
            // Se limit for 0, busca todas as consultas
            $consultas = $this->consultaModel->getByDominio($dominio, $limit);
            
            // Obter estatísticas
            $totalConsultas = $this->consultaModel->getTotalConsultas($dominio);
            $totalGasto = $this->consultaModel->getTotalGasto($dominio);

            jsonResponse([
                'success' => true,
                'consultas' => $consultas,
                'totalConsultas' => $totalConsultas,
                'totalGasto' => $totalGasto
            ]);
        } catch (Exception $e) {
            error_log("Erro ao listar consultas: " . $e->getMessage());
            jsonResponse(['error' => 'Erro ao processar a solicitação'], 500);
        }
    }

    /**
     * Obter estatísticas completas de todas as consultas
     */
    public function getEstatisticasConsultas()
    {
        // Obter domínio da query string (opcional)
        $dominio = isset($_GET['dominio']) ? $_GET['dominio'] : null;

        try {
            // Log para depuração
            $logFile = __DIR__ . '/../consultas_log.txt';
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Solicitação de estatísticas para domínio: " . ($dominio ?: 'todos') . "\n", FILE_APPEND);
            
            // Obter estatísticas
            $totalConsultas = $this->consultaModel->getTotalConsultas($dominio);
            $totalGasto = $this->consultaModel->getTotalGasto($dominio);
            
            // Verificar se foi solicitado para incluir as consultas
            $incluirConsultas = isset($_GET['incluirConsultas']) && $_GET['incluirConsultas'] === 'true';
            $consultas = [];
            
            if ($incluirConsultas) {
                if ($dominio) {
                    $consultas = $this->consultaModel->getByDominio($dominio, 0); // 0 para buscar todas
                } else {
                    // Se não foi especificado um domínio, buscar todas as consultas (limitado a 100)
                    $consultas = $this->consultaModel->getAll(100);
                }
                
                // Log do número de consultas encontradas
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Consultas encontradas: " . count($consultas) . "\n", FILE_APPEND);
            }

            jsonResponse([
                'success' => true,
                'totalConsultas' => $totalConsultas,
                'totalGasto' => $totalGasto,
                'dominio' => $dominio,
                'consultas' => $incluirConsultas ? $consultas : []
            ]);
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas de consultas: " . $e->getMessage());
            jsonResponse(['error' => 'Erro ao processar a solicitação'], 500);
        }
    }
}
