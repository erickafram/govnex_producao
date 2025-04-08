-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 07/04/2025 às 04:19
-- Versão do servidor: 8.3.0
-- Versão do PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `govnex`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `api_tokens`
--

DROP TABLE IF EXISTS `api_tokens`;
CREATE TABLE IF NOT EXISTS `api_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `api_tokens`
--

INSERT INTO `api_tokens` (`id`, `token`, `description`, `user_id`, `created_at`, `expires_at`, `is_active`) VALUES
(3, '8ab984d986b155d84b4f88dec6d4f8c3cd2e11c685d9805107df78e94ab488ca', NULL, NULL, '2025-03-24 01:49:41', NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `consultas_log`
--

DROP TABLE IF EXISTS `consultas_log`;
CREATE TABLE IF NOT EXISTS `consultas_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cnpj_consultado` varchar(14) NOT NULL,
  `dominio_origem` varchar(255) NOT NULL,
  `data_consulta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `custo` decimal(10,2) NOT NULL DEFAULT '0.05',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1300 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `consultas_log`
--

INSERT INTO `consultas_log` (`id`, `cnpj_consultado`, `dominio_origem`, `data_consulta`, `custo`) VALUES
(36, '60043704000173', 'infovisa.gurupi.to.gov.br', '2025-03-24 06:50:02', 0.05),
(1299, '98765432000121', 'infovisa.gurupi.to.gov.br', '2025-04-07 04:10:55', 0.12);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos`
--

DROP TABLE IF EXISTS `pagamentos`;
CREATE TABLE IF NOT EXISTS `pagamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('pendente','pago','cancelado') DEFAULT 'pendente',
  `codigo_transacao` varchar(255) NOT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `pagamentos`
--

INSERT INTO `pagamentos` (`id`, `usuario_id`, `valor`, `status`, `codigo_transacao`, `data_criacao`, `data_atualizacao`) VALUES
(32, 1, 200.00, 'pago', '3f8e4c72-5272-489e-be80-eb03b000463a', '2025-03-31 13:20:01', '2025-03-31 13:22:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessoes`
--

DROP TABLE IF EXISTS `sessoes`;
CREATE TABLE IF NOT EXISTS `sessoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiracao` datetime NOT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  KEY `idx_usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `sessoes`
--

INSERT INTO `sessoes` (`id`, `usuario_id`, `token`, `expiracao`, `data_criacao`) VALUES
(1, 1, '960ba405af1e4a24dd46d64b3263230d6928fd2385cb2dbdf70a156e873eb82c', '2025-04-13 13:36:23', '2025-04-06 13:36:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `dominio` varchar(255) DEFAULT NULL,
  `nivel_acesso` enum('visitante','assinante','administrador') DEFAULT 'visitante',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `credito` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `telefone`, `cpf`, `cnpj`, `senha`, `dominio`, `nivel_acesso`, `data_cadastro`, `credito`) VALUES
(1, 'FUNDO MUNICIPAL DE SAUDE', 'infovisa.gurupi@govnex.site', '6384478868', '10044370130', '11336672000199', '$2y$10$moxrgyqRjZFRMs482yf3KeaWJboP.1IH9hEv9VV3FtqoHCef5oD6W', 'infovisa.gurupi.to.gov.br', 'visitante', '2025-03-23 20:51:12', 147.50),
(2, 'Erick Vinicius Rodrigues', 'erickafram08@gmail.com', '(63) 98101-3083', '017.588.481-11', NULL, '$2y$10$moxrgyqRjZFRMs482yf3KeaWJboP.1IH9hEv9VV3FtqoHCef5oD6W', NULL, 'administrador', '2025-03-28 16:58:40', 0.00);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
