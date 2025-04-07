<?php
require_once __DIR__ . '/../config.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Criar um novo usuário
     * 
     * @param array $userData Dados do usuário
     * @return array|false Dados do usuário criado ou false em caso de erro
     */
    public function create($userData)
    {
        try {
            // Verificar se o email já existe
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $userData['email']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return false; // Email já existe
            }

            // Hash da senha
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            // Determinar se é CPF ou CNPJ
            $document = preg_replace('/[^0-9]/', '', $userData['document']);
            $cpf = null;
            $cnpj = null;

            if (strlen($document) == 11) {
                $cpf = $document;
            } else if (strlen($document) == 14) {
                $cnpj = $document;
            }

            // Inserir novo usuário
            $stmt = $this->db->prepare("
                INSERT INTO usuarios (nome, email, telefone, cpf, cnpj, senha, dominio, nivel_acesso, data_cadastro, credito)
                VALUES (:nome, :email, :telefone, :cpf, :cnpj, :senha, :dominio, 'visitante', NOW(), 0)
            ");

            $stmt->bindParam(':nome', $userData['name']);
            $stmt->bindParam(':email', $userData['email']);
            $stmt->bindParam(':telefone', $userData['phone']);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':cnpj', $cnpj);
            $stmt->bindParam(':senha', $hashedPassword);
            $stmt->bindParam(':dominio', $userData['domain']);
            $stmt->execute();

            $userId = $this->db->lastInsertId();

            // Retornar dados do usuário criado
            return $this->getById($userId);
        } catch (PDOException $e) {
            // Em produção, você deve registrar o erro e não exibi-lo
            error_log("Erro ao criar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter usuário pelo ID
     * 
     * @param int $id ID do usuário
     * @return array|false Dados do usuário ou false se não encontrado
     */
    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id, 
                    nome as name, 
                    email, 
                    COALESCE(cpf, cnpj) as document, 
                    telefone as phone, 
                    dominio as domain, 
                    credito as balance, 
                    (nivel_acesso = 'administrador') as isAdmin, 
                    data_cadastro as createdAt
                FROM usuarios
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $user = $stmt->fetch();
            if ($user) {
                // Converter isAdmin para booleano
                $user['isAdmin'] = (bool)$user['isAdmin'];
                return $user;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Autenticar usuário
     * 
     * @param string $email Email do usuário
     * @param string $password Senha do usuário
     * @return array|false Dados do usuário ou false se autenticação falhar
     */
    public function authenticate($email, $password)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id, 
                    nome as name, 
                    email, 
                    COALESCE(cpf, cnpj) as document, 
                    telefone as phone, 
                    dominio as domain, 
                    senha as password, 
                    credito as balance, 
                    (nivel_acesso = 'administrador') as isAdmin, 
                    data_cadastro as createdAt
                FROM usuarios
                WHERE email = :email
            ");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                // Remover a senha do resultado
                unset($user['password']);
                // Converter isAdmin para booleano
                $user['isAdmin'] = (bool)$user['isAdmin'];
                return $user;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erro na autenticação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar todos os usuários
     * 
     * @return array Lista de usuários
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    id, 
                    nome as name, 
                    email, 
                    COALESCE(cpf, cnpj) as document, 
                    telefone as phone, 
                    dominio as domain, 
                    credito as balance, 
                    (nivel_acesso = 'administrador') as isAdmin, 
                    data_cadastro as createdAt
                FROM usuarios
                ORDER BY nome
            ");

            $users = $stmt->fetchAll();
            foreach ($users as &$user) {
                $user['isAdmin'] = (bool)$user['isAdmin'];
            }

            return $users;
        } catch (PDOException $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [];
        }
    }
}
