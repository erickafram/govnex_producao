<?php
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
     */
    public function getConsultasDisponiveis()
    {
        // Obter ID do usuário da query string
        $userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;

        if ($userId <= 0) {
            jsonResponse(['error' => 'ID de usuário inválido'], 400);
        }

        try {
            // Obter usuário
            $user = $this->userModel->getById($userId);

            if (!$user) {
                jsonResponse(['error' => 'Usuário não encontrado'], 404);
            }

            // Calcular número de consultas disponíveis (cada consulta custa 0.12)
            $credito = floatval($user['balance']);
            $consultasDisponiveis = floor($credito / 0.12);

            // Obter histórico de consultas
            $dominio = $user['domain'] ?? null;
            $historicoConsultas = [];

            if ($dominio) {
                $historicoConsultas = $this->consultaModel->getByDominio($dominio, 10);
            }

            jsonResponse([
                'success' => true,
                'credito' => $credito,
                'consultasDisponiveis' => $consultasDisponiveis,
                'historicoConsultas' => $historicoConsultas
            ]);
        } catch (Exception $e) {
            error_log("Erro ao obter consultas: " . $e->getMessage());
            jsonResponse(['error' => 'Erro ao processar a solicitação'], 500);
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
            // Obter estatísticas
            $totalConsultas = $this->consultaModel->getTotalConsultas($dominio);
            $totalGasto = $this->consultaModel->getTotalGasto($dominio);
            
            // Verificar se foi solicitado para incluir as consultas
            $incluirConsultas = isset($_GET['incluirConsultas']) && $_GET['incluirConsultas'] === 'true';
            $consultas = [];
            
            if ($incluirConsultas && $dominio) {
                $consultas = $this->consultaModel->getByDominio($dominio, 0); // 0 para buscar todas
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
