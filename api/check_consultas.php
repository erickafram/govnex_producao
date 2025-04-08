<?php
require_once __DIR__ . '/db_config.php'''';

// Verificar conexão com o banco de dados
try {
    $db = getDbConnection();
    echo "Conexão com o banco de dados estabelecida com sucesso!\n";

    // Verificar tabela de consultas
    $stmt = $db->query("SHOW TABLES LIKE 'consultas_log'");
    if ($stmt->rowCount() > 0) {
        echo "Tabela 'consultas_log' encontrada.\n";

        // Contar consultas
        $stmt = $db->query("SELECT COUNT(*) as total FROM consultas_log");
        $result = $stmt->fetch();
        echo "Total de consultas registradas: " . $result['total'] . "\n";

        // Listar consultas para demonstração
        $stmt = $db->query("SELECT id, cnpj_consultado, dominio_origem, data_consulta, custo FROM consultas_log ORDER BY data_consulta DESC LIMIT 5");
        $consultas = $stmt->fetchAll();

        if (count($consultas) > 0) {
            echo "\nÚltimas consultas registradas:\n";
            echo str_repeat('-', 100) . "\n";
            echo sprintf("%-5s | %-20s | %-30s | %-25s | %-10s\n", "ID", "CNPJ", "Domínio", "Data", "Custo");
            echo str_repeat('-', 100) . "\n";

            foreach ($consultas as $consulta) {
                echo sprintf(
                    "%-5s | %-20s | %-30s | %-25s | %-10s\n",
                    $consulta['id'],
                    $consulta['cnpj_consultado'],
                    substr($consulta['dominio_origem'], 0, 30),
                    $consulta['data_consulta'],
                    $consulta['custo']
                );
            }
            echo str_repeat('-', 100) . "\n";
        } else {
            echo "Nenhuma consulta encontrada.\n";
        }
    } else {
        echo "Tabela 'consultas_log' não encontrada. Verifique se o banco de dados está configurado corretamente.\n";

        // Criar tabela de consultas
        echo "Deseja criar a tabela 'consultas_log'? (s/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) == 's') {
            $sql = "
            CREATE TABLE `consultas_log` (
              `id` int NOT NULL AUTO_INCREMENT,
              `cnpj_consultado` varchar(14) NOT NULL,
              `dominio_origem` varchar(255) NOT NULL,
              `data_consulta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              `custo` decimal(10,2) NOT NULL DEFAULT '0.05',
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
            ";

            $db->exec($sql);
            echo "Tabela 'consultas_log' criada com sucesso!\n";
        }
    }

    // Verificar usuários e seus domínios
    $stmt = $db->query("SELECT id, nome, email, dominio, credito FROM usuarios WHERE dominio IS NOT NULL");
    $users = $stmt->fetchAll();

    if (count($users) > 0) {
        echo "\nUsuários com domínio configurado (para consultas):\n";
        echo str_repeat('-', 100) . "\n";
        echo sprintf("%-5s | %-30s | %-30s | %-20s | %-10s\n", "ID", "Nome", "Email", "Domínio", "Crédito");
        echo str_repeat('-', 100) . "\n";

        foreach ($users as $user) {
            echo sprintf(
                "%-5s | %-30s | %-30s | %-20s | %-10s\n",
                $user['id'],
                substr($user['nome'], 0, 30),
                $user['email'],
                $user['dominio'],
                $user['credito']
            );
        }
        echo str_repeat('-', 100) . "\n";
    } else {
        echo "\nNenhum usuário com domínio configurado encontrado.\n";
        echo "Para registrar consultas, é necessário que o usuário tenha um domínio configurado.\n";
    }
} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage() . "\n";
    echo "Verifique as configurações no arquivo .env\n";
}
