-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25/05/2026 às 23:50
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `indux`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `alarmes`
--

CREATE TABLE `alarmes` (
  `id` int(10) UNSIGNED NOT NULL,
  `equipamento_id` int(10) UNSIGNED NOT NULL,
  `tipo` enum('temperatura','pressao','falha','conexao','manutencao') NOT NULL,
  `severidade` enum('critico','alerta','informativo') NOT NULL DEFAULT 'alerta',
  `mensagem` text NOT NULL,
  `valor_registrado` float DEFAULT NULL,
  `valor_limite` float DEFAULT NULL,
  `resolvido` tinyint(1) NOT NULL DEFAULT 0,
  `resolvido_por` int(10) UNSIGNED DEFAULT NULL,
  `resolvido_em` datetime DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `alarmes`
--

INSERT INTO `alarmes` (`id`, `equipamento_id`, `tipo`, `severidade`, `mensagem`, `valor_registrado`, `valor_limite`, `resolvido`, `resolvido_por`, `resolvido_em`, `criado_em`) VALUES
(1, 2, 'temperatura', 'critico', 'Temperatura acima do limite: 88.9°C (máx: 70°C)', 88.9, 70, 1, 2, '2026-05-25 18:25:02', '2026-05-25 12:37:51'),
(2, 2, 'pressao', 'critico', 'Pressão acima do limite crítico: 13.2 bar (máx: 12 bar)', 13.2, 12, 1, 1, '2026-05-25 14:52:57', '2026-05-25 12:37:51'),
(3, 1, 'manutencao', 'alerta', 'Manutenção preventiva programada para esta semana', NULL, NULL, 1, 2, '2026-05-25 18:27:22', '2026-05-25 12:37:51'),
(4, 5, 'pressao', 'alerta', 'Pressão em zona de alerta: 9.5 bar', 9.5, 10, 1, 2, '2026-05-25 18:27:20', '2026-05-25 14:57:27'),
(5, 5, 'pressao', 'alerta', 'Pressão em zona de alerta: 8.7 bar', 8.7, 10, 1, 2, '2026-05-25 18:27:17', '2026-05-25 14:59:08'),
(6, 2, 'temperatura', 'alerta', 'Temperatura em zona de alerta: 70°C', 70, 70, 0, NULL, NULL, '2026-05-25 18:35:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipamentos`
--

CREATE TABLE `equipamentos` (
  `id` int(10) UNSIGNED NOT NULL,
  `tag` varchar(50) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `fabricante` varchar(100) DEFAULT NULL,
  `localizacao` varchar(200) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('ativo','inativo','em_falha') NOT NULL DEFAULT 'ativo',
  `temp_min` float NOT NULL DEFAULT 0,
  `temp_max` float NOT NULL DEFAULT 80,
  `pressao_min` float NOT NULL DEFAULT 0,
  `pressao_max` float NOT NULL DEFAULT 10,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `equipamentos`
--

INSERT INTO `equipamentos` (`id`, `tag`, `nome`, `modelo`, `fabricante`, `localizacao`, `descricao`, `status`, `temp_min`, `temp_max`, `pressao_min`, `pressao_max`, `criado_em`) VALUES
(1, 'CLD-001', 'Caldeira Principal', 'CBR-5000', 'ThermoTec', 'Sala 01', '', 'ativo', 20, 95, 0, 15, '2026-05-25 12:37:51'),
(2, 'CMP-002', 'Compressor Industrial', 'AIR-2400', 'PneumaCorp', 'Área B', '', 'ativo', 15, 70, 0.01, 12, '2026-05-25 12:37:51'),
(3, 'BBA-003', 'Bomba Hidráulica', 'HYD-800', 'FluidTech', 'Subsolo', '', 'ativo', 10, 65, 1, 10, '2026-05-25 12:37:51'),
(4, 'TRF-004', 'Transformador Elétrico', 'TRF-500KVA', 'ElectraInd', 'Subestação', NULL, 'inativo', 0, 85, 0, 5, '2026-05-25 12:37:51'),
(5, 'ALK-540', 'Sensor Termico', 'JLK-1200', 'NIKE Ind.', 'Sala 01', '', 'ativo', 12, 95, 7.2, 10, '2026-05-25 14:56:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `leituras_sensor`
--

CREATE TABLE `leituras_sensor` (
  `id` int(10) UNSIGNED NOT NULL,
  `equipamento_id` int(10) UNSIGNED NOT NULL,
  `temperatura` float NOT NULL,
  `pressao` float NOT NULL,
  `umidade` float DEFAULT NULL,
  `registrado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `leituras_sensor`
--

INSERT INTO `leituras_sensor` (`id`, `equipamento_id`, `temperatura`, `pressao`, `umidade`, `registrado_em`) VALUES
(1, 1, 72.4, 8.1, 55, '2026-05-25 12:35:51'),
(2, 1, 70.1, 7.9, 54, '2026-05-25 12:30:51'),
(3, 1, 68.5, 7.5, 53, '2026-05-25 12:25:51'),
(4, 2, 88.9, 13.2, NULL, '2026-05-25 12:36:51'),
(5, 2, 85.2, 12.8, NULL, '2026-05-25 12:31:51'),
(6, 3, 45, 6.5, NULL, '2026-05-25 12:32:51'),
(7, 3, 44.2, 6.3, NULL, '2026-05-25 12:27:51'),
(8, 5, 75.1, 9.5, NULL, '2026-05-25 14:57:27'),
(9, 5, 55, 8.7, NULL, '2026-05-25 14:59:08'),
(10, 2, 70, 8, NULL, '2026-05-25 18:35:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_sistema`
--

CREATE TABLE `log_sistema` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `tabela_afetada` varchar(100) DEFAULT NULL,
  `registro_id` int(10) UNSIGNED DEFAULT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `log_sistema`
--

INSERT INTO `log_sistema` (`id`, `usuario_id`, `acao`, `tabela_afetada`, `registro_id`, `detalhes`, `ip`, `criado_em`) VALUES
(1, 1, 'CRIAR_USUARIO', 'usuarios', 2, 'Perfil:funcionario', '::1', '2026-05-25 12:40:35'),
(2, 1, 'LOGOUT', 'usuarios', 1, NULL, '::1', '2026-05-25 12:41:26'),
(3, 2, 'LOGIN', 'usuarios', 2, NULL, '::1', '2026-05-25 12:41:38'),
(4, 2, 'LOGOUT', 'usuarios', 2, NULL, '::1', '2026-05-25 12:44:13'),
(5, 1, 'LOGOUT', 'usuarios', 1, NULL, '::1', '2026-05-25 14:35:23'),
(6, 1, 'RESOLVER_ALARME', 'alarmes', 2, NULL, '::1', '2026-05-25 14:52:57'),
(7, 1, 'EDITAR_EQUIPAMENTO', 'equipamentos', 3, 'TAG:BBA-003', '::1', '2026-05-25 14:55:29'),
(8, 1, 'CRIAR_EQUIPAMENTO', 'equipamentos', 5, 'TAG:ALK-540', '::1', '2026-05-25 14:56:47'),
(9, 1, 'REGISTRAR_LEITURA', 'leituras_sensor', 5, 'T:75.1°C P:9.5bar', '::1', '2026-05-25 14:57:27'),
(10, 1, 'REGISTRAR_LEITURA', 'leituras_sensor', 5, 'T:55°C P:8.7bar', '::1', '2026-05-25 14:59:08'),
(11, 1, 'LOGOUT', 'usuarios', 1, NULL, '::1', '2026-05-25 18:22:03'),
(12, 2, 'LOGIN', 'usuarios', 2, NULL, '::1', '2026-05-25 18:22:17'),
(13, 2, 'EDITAR_EQUIPAMENTO', 'equipamentos', 2, 'TAG:CMP-002', '::1', '2026-05-25 18:24:14'),
(14, 2, 'RESOLVER_ALARME', 'alarmes', 1, NULL, '::1', '2026-05-25 18:25:02'),
(15, 2, 'EDITAR_EQUIPAMENTO', 'equipamentos', 2, 'TAG:CMP-002', '::1', '2026-05-25 18:26:22'),
(16, 2, 'EDITAR_EQUIPAMENTO', 'equipamentos', 2, 'TAG:CMP-002', '::1', '2026-05-25 18:27:04'),
(17, 2, 'RESOLVER_ALARME', 'alarmes', 5, NULL, '::1', '2026-05-25 18:27:17'),
(18, 2, 'RESOLVER_ALARME', 'alarmes', 4, NULL, '::1', '2026-05-25 18:27:20'),
(19, 2, 'RESOLVER_ALARME', 'alarmes', 3, NULL, '::1', '2026-05-25 18:27:22'),
(20, 2, 'EDITAR_EQUIPAMENTO', 'equipamentos', 2, 'TAG:CMP-002', '::1', '2026-05-25 18:27:48'),
(21, 2, 'EDITAR_EQUIPAMENTO', 'equipamentos', 2, 'TAG:CMP-002', '::1', '2026-05-25 18:29:11'),
(22, 2, 'ALTERAR_STATUS', 'equipamentos', 1, 'Novo status: em_falha', '::1', '2026-05-25 18:29:22'),
(23, 2, 'EDITAR_EQUIPAMENTO', 'equipamentos', 1, 'TAG:CLD-001', '::1', '2026-05-25 18:29:46'),
(24, 2, 'EDITAR_EQUIPAMENTO', 'equipamentos', 1, 'TAG:CLD-001', '::1', '2026-05-25 18:29:53'),
(25, 2, 'ALTERAR_STATUS', 'equipamentos', 1, 'Novo status: ativo', '::1', '2026-05-25 18:33:26'),
(26, 2, 'LOGOUT', 'usuarios', 2, NULL, '::1', '2026-05-25 18:34:25'),
(27, 1, 'REGISTRAR_LEITURA', 'leituras_sensor', 2, 'T:70°C P:8bar', '::1', '2026-05-25 18:35:51'),
(28, 1, 'EDITAR_USUARIO', 'usuarios', 2, 'Perfil:funcionario', '::1', '2026-05-25 18:48:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('admin','funcionario') NOT NULL DEFAULT 'funcionario',
  `foto` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acesso` datetime DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `is_operador` tinyint(1) DEFAULT 0,
  `perm_criar_equip` tinyint(1) DEFAULT 0,
  `perm_editar_equip` tinyint(1) DEFAULT 0,
  `perm_resolver_alarme` tinyint(1) DEFAULT 0,
  `tipo_plano` enum('starter','pro','enterprise') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `ativo`, `ultimo_acesso`, `criado_em`, `is_operador`, `perm_criar_equip`, `perm_editar_equip`, `perm_resolver_alarme`, `tipo_plano`) VALUES
(1, 'Administrador INDUX', 'admin@indux.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NULL, '2026-05-25 12:37:51', 0, 0, 0, 0, 'enterprise'),
(2, 'Roberivan Santos', 'roberivan@indux.com.br', '$2y$10$D9jPdlm5N6FxTQF8jfBeCOVH8AVW0jRZAlPBElM6gRTh03qivWQs2', 'funcionario', 1, '2026-05-25 18:22:17', '2026-05-25 12:40:35', 1, 1, 1, 1, 'enterprise');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alarmes`
--
ALTER TABLE `alarmes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resolvido_por` (`resolvido_por`),
  ADD KEY `idx_equip_status` (`equipamento_id`,`resolvido`),
  ADD KEY `idx_severidade` (`severidade`,`resolvido`);

--
-- Índices de tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag` (`tag`);

--
-- Índices de tabela `leituras_sensor`
--
ALTER TABLE `leituras_sensor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_equip_data` (`equipamento_id`,`registrado_em`);

--
-- Índices de tabela `log_sistema`
--
ALTER TABLE `log_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_acao` (`acao`),
  ADD KEY `idx_data` (`criado_em`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alarmes`
--
ALTER TABLE `alarmes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `leituras_sensor`
--
ALTER TABLE `leituras_sensor`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `log_sistema`
--
ALTER TABLE `log_sistema`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alarmes`
--
ALTER TABLE `alarmes`
  ADD CONSTRAINT `alarmes_ibfk_1` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alarmes_ibfk_2` FOREIGN KEY (`resolvido_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `leituras_sensor`
--
ALTER TABLE `leituras_sensor`
  ADD CONSTRAINT `leituras_sensor_ibfk_1` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `log_sistema`
--
ALTER TABLE `log_sistema`
  ADD CONSTRAINT `log_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
