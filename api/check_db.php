<?php
require_once __DIR__ . '/config.php';

// Verificar conexão com o banco de dados
try {
    $db = getDbConnection();
    echo "Conexão com o banco de dados estabelecida com sucesso!\n";

    // Verificar tabela de usuários
    $stmt = $db->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "Tabela 'usuarios' encontrada.\n";

        // Contar usuários
        $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch();
        echo "Total de usuários: " . $result['total'] . "\n";

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
        } else {
            echo "Nenhum usuário encontrado. Você precisa criar usuários para teste.\n";
        }
    } else {
        echo "Tabela 'usuarios' não encontrada. Verifique se o banco de dados está configurado corretamente.\n";
    }
} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage() . "\n";
    echo "Verifique as configurações no arquivo .env\n";
}
