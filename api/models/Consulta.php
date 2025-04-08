<?php
require_once __DIR__ . '/../db_config.php'; // Corrigido: removido as aspas extras e ajustado o caminho

class Consulta
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Registrar uma nova consulta de CNPJ
     * 
     * @param string $cnpj CNPJ consultado
     * @param string $dominio Domínio de origem da consulta
     * @param float $custo Custo da consulta (padrão: 0.12)
     * @return array|false Dados da consulta registrada ou false em caso de erro
     */
    public function registrarConsulta($cnpj, $dominio, $custo = 0.12)
    {
        try {
            // Inserir registro de consulta
            $stmt = $this->db->prepare("
                INSERT INTO consultas_log (cnpj_consultado, dominio_origem, data_consulta, custo)
                VALUES (:cnpj, :dominio, NOW(), :custo)
            ");

            $stmt->bindParam(':cnpj', $cnpj);
            $stmt->bindParam(':dominio', $dominio);
            $stmt->bindParam(':custo', $custo);
            $stmt->execute();

            $consultaId = $this->db->lastInsertId();

            // Debitar crédito do usuário
            $stmt = $this->db->prepare("
                UPDATE usuarios 
                SET credito = credito - :custo 
                WHERE dominio = :dominio AND credito >= :custo
            ");
            $stmt->bindParam(':custo', $custo);
            $stmt->bindParam(':dominio', $dominio);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                // Rollback - remover o registro de consulta se não houver crédito suficiente
                $stmt = $this->db->prepare("DELETE FROM consultas_log WHERE id = :id");
                $stmt->bindParam(':id', $consultaId);
                $stmt->execute();

                return false; // Crédito insuficiente
            }

            // Retornar dados da consulta registrada
            return $this->getById($consultaId);
        } catch (PDOException $e) {
            error_log("Erro ao registrar consulta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter consulta pelo ID
     * 
     * @param int $id ID da consulta
     * @return array|false Dados da consulta ou false se não encontrada
     */
    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id, 
                    cnpj_consultado, 
                    dominio_origem, 
                    data_consulta, 
                    custo
                FROM consultas_log
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erro ao buscar consulta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter consultas por domínio
     * 
     * @param string $dominio Domínio de origem
     * @param int $limit Limite de registros (padrão: 10, se 0: sem limite)
     * @return array Lista de consultas
     */
    public function getByDominio($dominio, $limit = 10)
    {
        try {
            $sql = "
                SELECT 
                    id, 
                    cnpj_consultado, 
                    dominio_origem, 
                    data_consulta, 
                    custo
                FROM consultas_log
                WHERE dominio_origem = :dominio
                ORDER BY data_consulta DESC
            ";
            
            // Adicionar limite apenas se for maior que zero
            if ($limit > 0) {
                $sql .= " LIMIT :limit";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':dominio', $dominio);
            
            if ($limit > 0) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }
            
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao listar consultas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar todas as consultas com limite opcional
     * 
     * @param int $limit Limite de registros (0 para sem limite)
     * @return array Lista de consultas
     */
    public function getAll($limit = 100)
    {
        try {
            $query = "SELECT * FROM consultas_log ORDER BY data_consulta DESC";
            
            if ($limit > 0) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $this->db->prepare($query);
            
            if ($limit > 0) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar todas as consultas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar se um usuário tem crédito suficiente para realizar consultas
     * 
     * @param string $dominio Domínio do usuário
     * @param float $custoConsulta Custo da consulta (padrão: 0.12)
     * @return bool True se tem crédito suficiente, false caso contrário
     */
    public function verificarCredito($dominio, $custoConsulta = 0.12)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT credito 
                FROM usuarios 
                WHERE dominio = :dominio
            ");
            $stmt->bindParam(':dominio', $dominio);
            $stmt->execute();

            $result = $stmt->fetch();
            if ($result && $result['credito'] >= $custoConsulta) {
                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erro ao verificar crédito: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter o total de consultas realizadas por um domínio
     * 
     * @param string $dominio Domínio de origem (opcional)
     * @return int Total de consultas realizadas
     */
    public function getTotalConsultas($dominio = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM consultas_log";
            $params = [];
            
            if ($dominio) {
                $sql .= " WHERE dominio_origem = :dominio";
                $params[':dominio'] = $dominio;
            }
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $result ? intval($result['total']) : 0;
        } catch (PDOException $e) {
            error_log("Erro ao buscar total de consultas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter o valor total gasto em consultas por um domínio
     * 
     * @param string $dominio Domínio de origem (opcional)
     * @return float Valor total gasto em consultas
     */
    public function getTotalGasto($dominio = null)
    {
        try {
            $sql = "SELECT SUM(custo) as total FROM consultas_log";
            $params = [];
            
            if ($dominio) {
                $sql .= " WHERE dominio_origem = :dominio";
                $params[':dominio'] = $dominio;
            }
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $result && $result['total'] !== null ? floatval($result['total']) : 0.00;
        } catch (PDOException $e) {
            error_log("Erro ao buscar total gasto em consultas: " . $e->getMessage());
            return 0.00;
        }
    }
}
