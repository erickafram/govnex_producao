<?php
require_once __DIR__ . '/../db_config.php'; // Corrigido: removido as aspas extras e ajustado o caminho

class ApiToken
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Gerar um novo token de API
     * 
     * @param string|null $description Descrição do token
     * @param int|null $userId ID do usuário associado
     * @param string|null $expiresAt Data de expiração (formato ISO)
     * @return array|false Token gerado ou false em caso de erro
     */
    public function generateToken($description = null, $userId = null, $expiresAt = null)
    {
        try {
            // Gerar token seguro
            $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimais
            
            $sql = "
                INSERT INTO api_tokens (token, description, user_id, created_at, expires_at, is_active)
                VALUES (:token, :description, :user_id, NOW(), :expires_at, TRUE)
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->execute();
            
            $tokenId = $this->db->lastInsertId();
            
            // Retornar o token criado
            return $this->getById($tokenId);
        } catch (PDOException $e) {
            error_log("Erro ao gerar token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter token pelo ID
     * 
     * @param int $id ID do token
     * @return array|false Dados do token ou false se não encontrado
     */
    public function getById($id)
    {
        try {
            $sql = "
                SELECT 
                    t.id, 
                    t.token, 
                    t.description, 
                    t.user_id,
                    IFNULL(u.name, '') as user_name,
                    t.created_at, 
                    t.expires_at, 
                    t.is_active
                FROM api_tokens t
                LEFT JOIN usuarios u ON t.user_id = u.id
                WHERE t.id = :id
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $token = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($token) {
                // Converter valores para tipos apropriados
                $token['id'] = (string)$token['id'];
                $token['is_active'] = (bool)$token['is_active'];
                $token['user_id'] = $token['user_id'] ? (string)$token['user_id'] : null;
            }
            
            return $token;
        } catch (PDOException $e) {
            error_log("Erro ao buscar token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter todos os tokens
     * 
     * @return array Lista de tokens
     */
    public function getAll()
    {
        try {
            error_log("ApiToken::getAll - Iniciando consulta de tokens");
            
            // Verificamos primeiro se a tabela existe
            $checkTable = $this->db->query("SHOW TABLES LIKE 'api_tokens'");
            if ($checkTable->rowCount() === 0) {
                error_log("ApiToken::getAll - Tabela 'api_tokens' não existe");
                // A tabela não existe, retornar array vazio
                return [];
            }
            
            $sql = "
                SELECT 
                    t.id, 
                    t.token, 
                    t.description, 
                    t.user_id,
                    IFNULL(u.name, '') as user_name,
                    t.created_at, 
                    t.expires_at, 
                    t.is_active
                FROM api_tokens t
                LEFT JOIN usuarios u ON t.user_id = u.id
                ORDER BY t.created_at DESC
            ";
            
            error_log("ApiToken::getAll - Executando SQL: " . $sql);
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("ApiToken::getAll - Tokens encontrados: " . count($tokens));
            
            // Converter valores para tipos apropriados
            foreach ($tokens as &$token) {
                $token['id'] = (string)$token['id'];
                $token['is_active'] = (bool)$token['is_active'];
                $token['user_id'] = $token['user_id'] ? (string)$token['user_id'] : null;
            }
            
            return $tokens;
        } catch (PDOException $e) {
            error_log("ApiToken::getAll - Erro ao listar tokens: " . $e->getMessage());
            return [];
        } catch (Exception $e) {
            error_log("ApiToken::getAll - Erro inesperado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Atualizar status do token
     * 
     * @param int $id ID do token
     * @param bool $isActive Novo status do token
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function updateStatus($id, $isActive)
    {
        try {
            $sql = "
                UPDATE api_tokens
                SET is_active = :is_active
                WHERE id = :id
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':is_active', $isActive, PDO::PARAM_BOOL);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status do token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se um token é válido
     * 
     * @param string $token Token a ser verificado
     * @return bool True se o token for válido, false caso contrário
     */
    public function validateToken($token)
    {
        try {
            $sql = "
                SELECT id
                FROM api_tokens
                WHERE token = :token
                  AND is_active = TRUE
                  AND (expires_at IS NULL OR expires_at > NOW())
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao validar token: " . $e->getMessage());
            return false;
        }
    }
} 
