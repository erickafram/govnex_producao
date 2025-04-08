-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 06-Abr-2025 às 12:49
-- Versão do servidor: 8.0.38
-- versão do PHP: 8.2.28

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
-- Estrutura da tabela `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` int NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `api_tokens`
--

INSERT INTO `api_tokens` (`id`, `token`, `created_at`, `is_active`) VALUES
(3, '8ab984d986b155d84b4f88dec6d4f8c3cd2e11c685d9805107df78e94ab488ca', '2025-03-24 01:49:41', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `consultas_log`
--

CREATE TABLE `consultas_log` (
  `id` int NOT NULL,
  `cnpj_consultado` varchar(14) NOT NULL,
  `dominio_origem` varchar(255) NOT NULL,
  `data_consulta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `custo` decimal(10,2) NOT NULL DEFAULT '0.05'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `consultas_log`
--

INSERT INTO `consultas_log` (`id`, `cnpj_consultado`, `dominio_origem`, `data_consulta`, `custo`) VALUES
(36, '60043704000173', 'infovisa.gurupi.to.gov.br', '2025-03-24 06:50:02', '0.05'),
(34, '47438705000159', 'infovisa.gurupi.to.gov.br', '2025-03-24 06:43:32', '0.05'),
(35, '47438705000159', 'infovisa.gurupi.to.gov.br', '2025-03-24 06:47:56', '0.05'),
(1265, '41829473000183', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:00:06', '0.12'),
(1266, '41829473000183', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:00:46', '0.12'),
(1267, '41829473000183', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:01:01', '0.12'),
(1268, '41829473000183', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:01:16', '0.12'),
(1269, '41829473000183', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:01:22', '0.12'),
(1270, '41829473000183', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:01:26', '0.12'),
(1271, '41829473000183', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:01:29', '0.12'),
(1272, '41829473000183', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:02:04', '0.12'),
(1273, '41829473000183', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:02:22', '0.12'),
(1274, '20529721000281', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:02:29', '0.12'),
(1275, '42615780000124', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:04:16', '0.12'),
(1276, '41910855000137', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:05:06', '0.12'),
(1277, '12073024000150', 'infovisa.gurupi.to.gov.br', '2025-04-05 04:05:55', '0.12'),
(1278, '35140032000177', 'infovisa.gurupi.to.gov.br', '2025-04-05 15:45:23', '0.12'),
(1279, '47418416000198', 'infovisa.gurupi.to.gov.br', '2025-04-05 16:12:31', '0.12'),
(1280, '02054114000140', 'infovisa.gurupi.to.gov.br', '2025-04-05 16:15:58', '0.12'),
(1281, '32191816000136', 'infovisa.gurupi.to.gov.br', '2025-04-05 16:16:17', '0.12'),
(1282, '47418416000198', 'infovisa.gurupi.to.gov.br', '2025-04-05 23:03:17', '0.12'),
(1283, '57130091000105', 'infovisa.gurupi.to.gov.br', '2025-04-05 23:03:38', '0.12'),
(1284, '35573392000162', 'infovisa.gurupi.to.gov.br', '2025-04-05 23:36:17', '0.12'),
(1285, '57130091000105', 'infovisa.gurupi.to.gov.br', '2025-04-05 23:36:45', '0.12'),
(1286, '35573392000162', 'infovisa.gurupi.to.gov.br', '2025-04-05 23:47:01', '0.12'),
(1287, '47418416000198', 'infovisa.gurupi.to.gov.br', '2025-04-05 23:47:37', '0.12'),
(1288, '47418416000198', 'infovisa.gurupi.to.gov.br', '2025-04-06 00:02:43', '0.12'),
(1289, '47418416000198', 'infovisa.gurupi.to.gov.br', '2025-04-06 00:04:33', '0.12'),
(1290, '47418416000198', 'infovisa.gurupi.to.gov.br', '2025-04-06 00:05:23', '0.12'),
(1291, '36673809000121', 'infovisa.gurupi.to.gov.br', '2025-04-06 00:06:51', '0.12'),
(1292, '36673809000121', 'infovisa.gurupi.to.gov.br', '2025-04-06 00:07:08', '0.12'),
(1293, '36673809000121', 'infovisa.gurupi.to.gov.br', '2025-04-06 00:08:45', '0.12');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('pendente','pago','cancelado') DEFAULT 'pendente',
  `codigo_transacao` varchar(255) NOT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `pagamentos`
--

INSERT INTO `pagamentos` (`id`, `usuario_id`, `valor`, `status`, `codigo_transacao`, `data_criacao`, `data_atualizacao`) VALUES
(32, 1, '200.00', 'pago', '3f8e4c72-5272-489e-be80-eb03b000463a', '2025-03-31 13:20:01', '2025-03-31 13:22:44');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `dominio` varchar(255) DEFAULT NULL,
  `nivel_acesso` enum('visitante','assinante','administrador') DEFAULT 'visitante',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `credito` decimal(10,2) NOT NULL DEFAULT '0.00'
) ;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `telefone`, `cpf`, `cnpj`, `senha`, `dominio`, `nivel_acesso`, `data_cadastro`, `credito`) VALUES
(1, 'FUNDO MUNICIPAL DE SAUDE', 'infovisa.gurupi@govnex.site', '6384478868', '10044370130', '11336672000199', '$2y$10$moxrgyqRjZFRMs482yf3KeaWJboP.1IH9hEv9VV3FtqoHCef5oD6W', 'infovisa.gurupi.to.gov.br', 'visitante', '2025-03-23 20:51:12', '142.50'),
(2, 'Erick Vinicius Rodrigues', 'erickafram08@gmail.com', '(63) 98101-3083', '017.588.481-11', NULL, '$2y$10$QA29w1LD16.va8pEgEH3..CACGBHVPSnuynXXL7zZY0yDgjiZE6V.', NULL, 'administrador', '2025-03-28 16:58:40', '0.00');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Índices para tabela `consultas_log`
--
ALTER TABLE `consultas_log`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `consultas_log`
--
ALTER TABLE `consultas_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1294;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
