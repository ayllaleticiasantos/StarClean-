-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 26/10/2025 às 23:20
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
-- Banco de dados: `db_starclean`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administrador`
--ALTER TABLE `avaliacao_prestador` ADD `oculto` TINYINT(1) NOT NULL DEFAULT 0;


CREATE TABLE `administrador` (
  `id` int(11) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `sobrenome` varchar(45) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo` enum('adminmaster','adminusuario','adminmoderador') NOT NULL DEFAULT 'adminmaster',
  `receber_notificacoes_email` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `administrador`
--

INSERT INTO `administrador` (`id`, `nome`, `sobrenome`, `email`, `password`, `tipo`, `receber_notificacoes_email`, `criado_em`, `atualizado_em`) VALUES
(1, 'Aylla Leticia dos Santos', 'Vieira', 'ayllasantosdf@hotmail.com', '$2y$10$zNX6FS1uuyWGJMaZCMUP/eImpO/mi.mm/sKrcODJfcjTGXnVzMDfe', 'adminmaster', 1, '2025-10-06 22:37:24', '2025-10-06 22:37:24'),
(2, 'StarClean', 'Serviços', 'starclean.prest.servicos@gmail.com', '$2y$10$jA7qjUoRmFJ.Ri6YL4V8GufisxsNyKvxDm87wIj6mQkbeeReU0CwO', 'adminmaster', 1, '2025-10-06 23:07:13', '2025-10-06 23:07:13');

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamento`
--

CREATE TABLE `agendamento` (
  `id` int(11) NOT NULL,
  `Cliente_id` int(11) NOT NULL,
  `Prestador_id` int(11) NOT NULL,
  `Servico_id` int(11) NOT NULL,
  `Endereco_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `status` enum('pendente','aceito','realizado','cancelado') NOT NULL DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `agendamento`
--

INSERT INTO `agendamento` (`id`, `Cliente_id`, `Prestador_id`, `Servico_id`, `Endereco_id`, `data`, `hora`, `status`, `observacoes`) VALUES
(1, 2, 1, 15, 20, '2025-10-24', '12:00:00', 'realizado', 'Limpeza de 3 salas comerciais.'),
(2, 4, 1, 15, 16, '2025-10-31', '13:00:00', 'cancelado', 'escritorio\r\n'),
(3, 4, 1, 15, 16, '2025-10-30', '12:00:00', 'cancelado', ''),
(4, 4, 4, 16, 19, '2025-10-18', '12:00:00', 'cancelado', ''),
(5, 4, 4, 16, 16, '2025-10-28', '12:03:00', 'realizado', ''),
(6, 4, 5, 18, 16, '2025-11-07', '15:00:00', 'pendente', 'Gostaria que ficasse, vindo sempre as sextas feiras.'),
(7, 4, 4, 38, 16, '2025-10-31', '19:00:00', 'aceito', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacao_prestador`
--

CREATE TABLE `avaliacao_prestador` (
  `id` int(11) NOT NULL,
  `Cliente_id` int(11) NOT NULL,
  `Prestador_id` int(11) NOT NULL,
  `nota` int(11) DEFAULT NULL,
  `comentario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `avaliacao_prestador`
--

INSERT INTO `avaliacao_prestador` (`id`, `Cliente_id`, `Prestador_id`, `nota`, `comentario`) VALUES
(1, 4, 4, 5, 'A limpeza mensal foi excelente, como sempre. A profissional foi muito eficiente e deixou tudo impecável, com atenção especial aos detalhes. O serviço contínuo é de alta qualidade e estou muito satisfeito. Obrigada Maria!');

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacao_servico`
--

CREATE TABLE `avaliacao_servico` (
  `id` int(11) NOT NULL,
  `Cliente_id` int(11) NOT NULL,
  `Servico_id` int(11) NOT NULL,
  `nota` int(11) DEFAULT NULL,
  `comentario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente`
--

CREATE TABLE `cliente` (
  `id` int(11) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `sobrenome` varchar(45) NOT NULL,
  `email` varchar(150) NOT NULL,
  `data_nascimento` date NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `password` varchar(255) NOT NULL,
  `receber_notificacoes_email` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `cliente`
--

INSERT INTO `cliente` (`id`, `nome`, `sobrenome`, `email`, `data_nascimento`, `telefone`, `cpf`, `password`, `receber_notificacoes_email`, `criado_em`, `atualizado_em`) VALUES
(2, 'Allana', 'Larissa', 'allanalarissa5@gmail.com', '2005-03-30', '(61)99181-7265', '077.469.001-19', '$2y$10$k3mmEMEj3zo3jPp4lt4WsOS99z8OZIdCRFgNvbS67FT.cH3.aJ6KG', 1, '2025-10-07 02:48:05', '2025-10-26 11:52:04'),
(4, 'Pedro Lucas dos Santos', 'Vieira', 'lucaspedro1030@gmail.com', '2008-10-30', '(61) 99402-4265', '077.469.451-39', '$2y$10$t9tXsVxBXOIQDxryciP.YujHQLpvbAOwIbZwlfG61XZFkUjs1Gj52', 1, '2025-10-18 01:21:02', '2025-10-18 01:21:02');

-- --------------------------------------------------------

--
-- Estrutura para tabela `disponibilidade`
--

CREATE TABLE `disponibilidade` (
  `id` int(11) NOT NULL,
  `Prestador_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `status` enum('livre','ocupado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `endereco`
--

CREATE TABLE `endereco` (
  `id` int(11) NOT NULL,
  `Cliente_id` int(11) NOT NULL,
  `Prestador_id` int(11) DEFAULT NULL,
  `cep` varchar(9) NOT NULL,
  `logradouro` varchar(250) DEFAULT NULL,
  `bairro` varchar(45) DEFAULT NULL,
  `cidade` varchar(45) DEFAULT NULL,
  `uf` char(2) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `endereco`
--

INSERT INTO `endereco` (`id`, `Cliente_id`, `Prestador_id`, `cep`, `logradouro`, `bairro`, `cidade`, `uf`, `numero`, `complemento`, `criado_em`, `atualizado_em`) VALUES
(16, 4, NULL, '72238369', 'Quadra SHPS Quadra 603 Conjunto C', 'Setor Habitacional Pôr do Sol (Ceilândia)', 'Brasília', 'DF', '18', 'Conjunto C', '2025-10-21 00:40:48', '2025-10-21 22:42:36'),
(19, 4, NULL, '72238360', 'Quadra SHPS Quadra 603', 'Setor Habitacional Pôr do Sol (Ceilândia)', 'Brasília', 'DF', '603', 'Conjunto C', '2025-10-21 22:43:18', '2025-10-21 22:43:18'),
(20, 2, NULL, '72231117', 'Quadra QNP 10 Conjunto Q', 'Ceilândia Sul (Ceilândia)', 'Brasília', 'DF', '46', 'P.sul', '2025-10-23 02:29:54', '2025-10-23 02:29:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `prestador`
--

CREATE TABLE `prestador` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `sobrenome` varchar(100) NOT NULL,
  `cpf` varchar(18) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `especialidade` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `receber_notificacoes_email` tinyint(1) NOT NULL DEFAULT 1,
  `descricao` text NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Administrador_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `prestador`
--

INSERT INTO `prestador` (`id`, `nome`, `sobrenome`, `cpf`, `email`, `telefone`, `especialidade`, `password`, `receber_notificacoes_email`, `descricao`, `criado_em`, `atualizado_em`, `Administrador_id`) VALUES
(1, 'Leticia Santos', 'Santos', '071.818.111-50', 'jeleticiasantosdf@gmail.com', '6130428546', 'Limpeza de Ambientes Pequenos', '$2y$10$clSfuBRszNZyO4tTpaDLzOQ7ggVLuyaqDxHIqpnetbXTfsjYii2WO', 1, 'Profissional do ramo de limpeza de casas, pequenas e minimalistas desde o ano de 2016, com foco em impermeabilização garantindo que sua casa fique mais limpa, por mais tempo.', '2025-10-07 02:24:18', '2025-10-26 11:58:33', 1),
(4, 'Maria Francivânia dos Santos', 'Rocha', '807.991.401-04', 'vaniasantosrocha@outlook.com', '(61) 99345-8309', 'Limpeza de Ambientes Pequenos', '$2y$10$KI3LXORf.8XxRrQJ9o3lz.5l5DfBv80/tSZpzgQB4dh74kcl.awvS', 1, 'Sou profissional da área da limpeza a mais de 20 anos, trabalho normalmente em casas de familia. Estou buscando a StarClean Pela oportunidade de me conectar facilmente com a carta de clientes da empresa e poder usufruir dos equipamentos.', '2025-10-23 03:39:21', '2025-10-23 03:39:21', 1),
(5, 'Arthur Luís', 'Dos Santos Rocha', '091.191.781-07', 'arthur@gmail.com', '(61) 99189-2912', 'Limpeza de Casas', '$2y$10$G4XGYvwR4xdVo7U5FQEJN./NQwlJgK1jkBIPGyO13CqpLjmgAKvDW', 1, 'Sou profissional da área da Limpeza, há 8 meses e busco na StarClean uma cartela de clientes vasta.', '2025-10-25 22:56:55', '2025-10-25 22:56:55', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `redefinicao_senha`
--

CREATE TABLE `redefinicao_senha` (
  `id` int(11) NOT NULL,
  `email` varchar(191) NOT NULL,
  `token` varchar(255) NOT NULL,
  `data_expiracao` datetime NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `redefinicao_senha`
--

INSERT INTO `redefinicao_senha` (`id`, `email`, `token`, `data_expiracao`, `criado_em`) VALUES
(4, 'ayllasantosdf@hotmail.com', '2fae7eae96e2c11e96629f7f966a1b1945119335f396ee5daf20a2d58e6662a47b8e14d20205d9ad2d8674d578418d7ca223', '2025-10-21 02:46:24', '2025-10-20 23:46:24'),
(6, 'allanalarissa5@gmail.com', '323f4c7303abff7c770f7c965ecfac4a3d54e73393c986dbdfca27f79d26ce14bc520a3c01b31baf90e247140f0636614457', '2025-10-26 20:27:51', '2025-10-26 18:27:51'),
(9, 'jeleticiasantosdf@gmail.com', 'c122929aa0261999e0e17392bee3fb4a05d818303aa55f2009ad48eba3b204b64f4743e845728b0c637bda17a50dc555a4ae', '2025-10-26 22:05:06', '2025-10-26 20:05:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servico`
--

CREATE TABLE `servico` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text NOT NULL,
  `preco` double NOT NULL,
  `duracao_estimada` time DEFAULT NULL,
  `prestador_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `servico`
--

INSERT INTO `servico` (`id`, `titulo`, `descricao`, `preco`, `duracao_estimada`, `prestador_id`) VALUES
(15, 'Combo 1 nível básico', 'Para escritório de 3 salas - Diária', 260, NULL, 1),
(16, 'Combo 1 nível básico', 'Para casa de 1 quarto mensal', 720, NULL, 4),
(17, 'Combo 1 nível básico', 'Para casa de 3 quartos diária', 270, NULL, 5),
(18, 'Combo 1 nível básico', 'Para casa de 3 quartos mensal', 1080, NULL, 5),
(19, 'Combo 1 nível básico', 'Para casa de 4 quartos diária', 320, NULL, 4),
(20, 'Combo 1 nível básico', 'Para casa de 4 quartos mensal', 1280, NULL, 4),
(21, 'Combo 1 nível básico', 'Para casa de +4 quartos diária', 450, NULL, 5),
(22, 'Combo 1 nível básico', 'Para casa de +4 quartos mensal', 1800, NULL, 5),
(23, 'Combo 1 nível básico', 'Para escritório de 1 sala diária', 190, NULL, 1),
(24, 'Combo 1 nível básico', 'Para escritório de 1 sala mensal', 760, NULL, 1),
(25, 'Combo 1 nível básico', 'Para escritório de 3 salas diária', 260, NULL, 1),
(26, 'Combo 1 nível básico', 'Para escritório de 3 salas mensal', 1040, NULL, 1),
(27, 'Combo 1 nível básico', 'Para escritório de 4 salas diária', 300, NULL, 5),
(28, 'Combo 1 nível básico', 'Para escritório de 4 salas mensal', 1200, NULL, 5),
(29, 'Combo 1 nível básico', 'Para escritório de +4 salas diária', 400, NULL, 4),
(30, 'Combo 1 nível básico', 'Para escritório de +4 salas mensal', 1600, NULL, 4),
(31, 'Combo 2 nível intermediário', 'Para casa de 2 quartos diária', 270, NULL, 1),
(32, 'Combo 2 nível intermediário', 'Para casa de 2 quartos mensal', 1000, NULL, 1),
(33, 'Combo 2 nível intermediário', 'para casa de 3 quartos diária', 300, NULL, 1),
(34, 'Combo 2 nível intermediário', 'Para casa de 3 quartos mensal', 1200, NULL, 1),
(35, 'Combo 2 nível intermediário', 'Para casa de 4 quartos diária', 350, NULL, 5),
(36, 'Combo 2 nível intermediário', 'Para casa de 4 quartos mensal', 1400, NULL, 5),
(37, 'Combo 2 nível intermediário', 'Para casa de +4 quartos mensal', 2000, NULL, 4),
(38, 'Combo 2 nível intermediário', 'Para casa de +4 quartos diária', 500, NULL, 4),
(39, 'Combo 2 nível intermediário', 'Para escritório de 2 salas diária', 260, NULL, 5);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- Índices de tabela `agendamento`
--
ALTER TABLE `agendamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Agendamento_Cliente1_idx` (`Cliente_id`),
  ADD KEY `fk_Agendamento_Prestador1_idx` (`Prestador_id`),
  ADD KEY `fk_Agendamento_Endereco1_idx` (`Endereco_id`),
  ADD KEY `fk_Agendamento_Servico1_idx` (`Servico_id`);

--
-- Índices de tabela `avaliacao_prestador`
--
ALTER TABLE `avaliacao_prestador`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Avaliacao_prestador_Prestador1_idx` (`Prestador_id`),
  ADD KEY `fk_Avaliacao_prestador_Cliente1_idx` (`Cliente_id`);

--
-- Índices de tabela `avaliacao_servico`
--
ALTER TABLE `avaliacao_servico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Avaliacao_servico_Servico1_idx` (`Servico_id`),
  ADD KEY `fk_Avaliacao_servico_Cliente1_idx` (`Cliente_id`);

--
-- Índices de tabela `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf_UNIQUE` (`cpf`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- Índices de tabela `disponibilidade`
--
ALTER TABLE `disponibilidade`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Disponibilidade_Prestador1_idx` (`Prestador_id`);

--
-- Índices de tabela `endereco`
--
ALTER TABLE `endereco`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Endereco_Cliente_idx` (`Cliente_id`),
  ADD KEY `fk_Endereco_Prestador1_idx` (`Prestador_id`);

--
-- Índices de tabela `prestador`
--
ALTER TABLE `prestador`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`),
  ADD UNIQUE KEY `cpf_cnpj_UNIQUE` (`cpf`),
  ADD KEY `fk_Prestador_Administrador1_idx` (`Administrador_id`);

--
-- Índices de tabela `redefinicao_senha`
--
ALTER TABLE `redefinicao_senha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`token`);

--
-- Índices de tabela `servico`
--
ALTER TABLE `servico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_servico_prestador1_idx` (`prestador_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `agendamento`
--
ALTER TABLE `agendamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `avaliacao_prestador`
--
ALTER TABLE `avaliacao_prestador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `avaliacao_servico`
--
ALTER TABLE `avaliacao_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `disponibilidade`
--
ALTER TABLE `disponibilidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `endereco`
--
ALTER TABLE `endereco`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `prestador`
--
ALTER TABLE `prestador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `redefinicao_senha`
--
ALTER TABLE `redefinicao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `servico`
--
ALTER TABLE `servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamento`
--
ALTER TABLE `agendamento`
  ADD CONSTRAINT `fk_Agendamento_Cliente1` FOREIGN KEY (`Cliente_id`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Agendamento_Endereco1` FOREIGN KEY (`Endereco_id`) REFERENCES `endereco` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Agendamento_Prestador1` FOREIGN KEY (`Prestador_id`) REFERENCES `prestador` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Agendamento_Servico1` FOREIGN KEY (`Servico_id`) REFERENCES `servico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `avaliacao_prestador`
--
ALTER TABLE `avaliacao_prestador`
  ADD CONSTRAINT `fk_Avaliacao_prestador_Cliente1` FOREIGN KEY (`Cliente_id`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Avaliacao_prestador_Prestador1` FOREIGN KEY (`Prestador_id`) REFERENCES `prestador` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `avaliacao_servico`
--
ALTER TABLE `avaliacao_servico`
  ADD CONSTRAINT `fk_Avaliacao_servico_Cliente1` FOREIGN KEY (`Cliente_id`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Avaliacao_servico_Servico1` FOREIGN KEY (`Servico_id`) REFERENCES `servico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `disponibilidade`
--
ALTER TABLE `disponibilidade`
  ADD CONSTRAINT `fk_Disponibilidade_Prestador1` FOREIGN KEY (`Prestador_id`) REFERENCES `prestador` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `endereco`
--
ALTER TABLE `endereco`
  ADD CONSTRAINT `fk_Endereco_Cliente` FOREIGN KEY (`Cliente_id`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Endereco_Prestador1` FOREIGN KEY (`Prestador_id`) REFERENCES `prestador` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `prestador`
--
ALTER TABLE `prestador`
  ADD CONSTRAINT `fk_Prestador_Administrador1` FOREIGN KEY (`Administrador_id`) REFERENCES `administrador` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `servico`
--
ALTER TABLE `servico`
  ADD CONSTRAINT `fk_servico_prestador1` FOREIGN KEY (`prestador_id`) REFERENCES `prestador` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- DECIMAL(10, 8) e DECIMAL(11, 8) são tipos de dados padrão para alta precisão de coordenadas geográficas.

ALTER TABLE endereco
ADD COLUMN latitude DECIMAL(10, 8) NULL AFTER complemento,
ADD COLUMN longitude DECIMAL(11, 8) NULL;

ALTER TABLE `avaliacao_prestador` ADD `oculto` TINYINT(1) NOT NULL DEFAULT 0;
