<?php
require_once __DIR__ . '/../models/User.php';

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Registrar um novo usuário
     */
    public function register()
    {
        // Obter dados da requisição
        $data = getRequestData();

        // Validar dados obrigatórios
        $requiredFields = ['name', 'email', 'document', 'phone', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                jsonResponse(['error' => "O campo {$field} é obrigatório"], 400);
            }
        }

        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Email inválido'], 400);
        }

        // Validar documento (CPF/CNPJ)
        $document = preg_replace('/[^0-9]/', '', $data['document']);
        if (strlen($document) != 11 && strlen($document) != 14) {
            jsonResponse(['error' => 'CPF/CNPJ inválido'], 400);
        }

        // Validar telefone
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            jsonResponse(['error' => 'Telefone inválido'], 400);
        }

        // Validar senha
        if (strlen($data['password']) < 6) {
            jsonResponse(['error' => 'A senha deve ter pelo menos 6 caracteres'], 400);
        }

        // Criar usuário
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'document' => $document,
            'phone' => $phone,
            'domain' => isset($data['domain']) ? $data['domain'] : null,
            'password' => $data['password']
        ];

        $user = $this->userModel->create($userData);

        if ($user) {
            jsonResponse([
                'success' => true,
                'message' => 'Usuário registrado com sucesso',
                'user' => $user
            ], 201);
        } else {
            jsonResponse(['error' => 'Falha ao registrar usuário. O email pode já estar em uso.'], 400);
        }
    }

    /**
     * Autenticar usuário
     */
    public function login()
    {
        // Obter dados da requisição
        $data = getRequestData();

        // Validar dados obrigatórios
        if (empty($data['email']) || empty($data['password'])) {
            jsonResponse(['error' => 'Email e senha são obrigatórios'], 400);
        }

        // Autenticar usuário
        $user = $this->userModel->authenticate($data['email'], $data['password']);

        if ($user) {
            jsonResponse([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'user' => $user
            ]);
        } else {
            jsonResponse(['error' => 'Credenciais inválidas'], 401);
        }
    }

    /**
     * Listar todos os usuários (apenas para admin)
     */
    public function listAll()
    {
        // Em um sistema real, você deve verificar se o usuário é admin
        // Isso pode ser feito com autenticação JWT ou sessões

        $users = $this->userModel->getAll();
        jsonResponse(['users' => $users]);
    }

    /**
     * Obter usuário pelo ID
     */
    public function getUser($id)
    {
        // Em um sistema real, você deve verificar se o usuário tem permissão
        // para acessar esses dados

        $user = $this->userModel->getById($id);

        if ($user) {
            jsonResponse(['user' => $user]);
        } else {
            jsonResponse(['error' => 'Usuário não encontrado'], 404);
        }
    }
}
