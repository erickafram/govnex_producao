<?php
require_once __DIR__ . '/config.php';

// Função para adicionar usuário de demonstração
function addDemoUser($db, $userData)
{
    try {
        // Verificar se o email já existe
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $userData['email']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Usuário com email {$userData['email']} já existe.\n";
            return false;
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
        $stmt = $db->prepare("
            INSERT INTO usuarios (nome, email, telefone, cpf, cnpj, senha, dominio, nivel_acesso, data_cadastro, credito)
            VALUES (:nome, :email, :telefone, :cpf, :cnpj, :senha, :dominio, :nivel_acesso, NOW(), :credito)
        ");

        $stmt->bindParam(':nome', $userData['name']);
        $stmt->bindParam(':email', $userData['email']);
        $stmt->bindParam(':telefone', $userData['phone']);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':cnpj', $cnpj);
        $stmt->bindParam(':senha', $hashedPassword);
        $stmt->bindParam(':dominio', $userData['domain']);
        $stmt->bindParam(':nivel_acesso', $userData['access_level']);
        $stmt->bindParam(':credito', $userData['balance']);
        $stmt->execute();

        echo "Usuário {$userData['name']} ({$userData['email']}) adicionado com sucesso!\n";
        return true;
    } catch (PDOException $e) {
        echo "Erro ao adicionar usuário {$userData['email']}: " . $e->getMessage() . "\n";
        return false;
    }
}

// Verificar conexão com o banco de dados
try {
    $db = getDbConnection();
    echo "Conexão com o banco de dados estabelecida com sucesso!\n";

    // Usuários de demonstração
    $demoUsers = [
        [
            'name' => 'Usuário Comum',
            'email' => 'user@example.com',
            'phone' => '11999999999',
            'document' => '12345678901', // CPF
            'domain' => null,
            'password' => 'password',
            'access_level' => 'visitante',
            'balance' => 100.00
        ],
        [
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'phone' => '11988888888',
            'document' => '98765432101', // CPF
            'domain' => null,
            'password' => 'password',
            'access_level' => 'administrador',
            'balance' => 500.00
        ]
    ];

    // Adicionar usuários de demonstração
    foreach ($demoUsers as $userData) {
        addDemoUser($db, $userData);
    }

    // Listar usuários para demonstração
    $stmt = $db->query("SELECT id, nome, email, nivel_acesso, credito FROM usuarios LIMIT 5");
    $users = $stmt->fetchAll();

    if (count($users) > 0) {
        echo "\nUsuários disponíveis para teste:\n";
        echo str_repeat('-', 80) . "\n";
        echo sprintf("%-5s | %-30s | %-30s | %-15s | %-10s\n", "ID", "Nome", "Email", "Nível", "Crédito");
        echo str_repeat('-', 80) . "\n";

        foreach ($users as $user) {
            echo sprintf(
                "%-5s | %-30s | %-30s | %-15s | %-10s\n",
                $user['id'],
                substr($user['nome'], 0, 30),
                $user['email'],
                $user['nivel_acesso'],
                $user['credito']
            );
        }
        echo str_repeat('-', 80) . "\n";
    }

    echo "\nCredenciais para teste:\n";
    echo "- Usuário comum: user@example.com / password\n";
    echo "- Administrador: admin@example.com / password\n";
} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage() . "\n";
    echo "Verifique as configurações no arquivo .env\n";
}
