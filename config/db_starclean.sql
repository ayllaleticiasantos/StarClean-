-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/11/2025 às 17:10
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
--

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
(1, 'Aylla Leticia dos Santos', 'Vieira', 'ayllasantosdf@hotmail.com', '$2y$10$ayexAn5jZ2gXZdXd4uMV9uwsoQYIUFyvcy5yf9eyvi/qt3/aoyj/C', 'adminmaster', 1, '2025-10-06 22:37:24', '2025-11-02 19:28:13'),
(2, 'StarClean', 'Serviços', 'starclean.prest.servicos@gmail.com', '$2y$10$qeEKq77HSawARhHTbWPGDO/3TJnMpn1js3WiCoU5yqQ9egcItvYMy', 'adminusuario', 1, '2025-10-06 23:07:13', '2025-11-02 22:32:28');

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
  `status` enum('pendente','aceito','realizado','cancelado','remarcado') NOT NULL DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL,
  `tem_pets` tinyint(1) NOT NULL DEFAULT 0,
  `tem_crianca` tinyint(1) NOT NULL DEFAULT 0,
  `possui_aspirador` tinyint(1) NOT NULL DEFAULT 0,
  `notificacao_prestador_lida` tinyint(1) NOT NULL DEFAULT 0,
  `notificacao_cliente_lida` tinyint(1) NOT NULL DEFAULT 0,
  `notificacao_admin_lida` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `agendamento`
--

INSERT INTO `agendamento` (`id`, `Cliente_id`, `Prestador_id`, `Servico_id`, `Endereco_id`, `data`, `hora`, `status`, `observacoes`, `tem_pets`, `tem_crianca`, `possui_aspirador`, `notificacao_prestador_lida`, `notificacao_cliente_lida`, `notificacao_admin_lida`) VALUES
(1, 2, 1, 15, 20, '2025-10-24', '12:00:00', 'realizado', 'Limpeza de 3 salas comerciais.', 0, 0, 0, 0, 0, 1),
(2, 4, 1, 15, 16, '2025-10-31', '13:00:00', 'cancelado', 'escritorio\r\n', 0, 0, 0, 0, 0, 0),
(3, 4, 1, 15, 16, '2025-10-30', '12:00:00', 'cancelado', '', 0, 0, 0, 0, 0, 0),
(4, 4, 4, 16, 19, '2025-10-18', '12:00:00', 'cancelado', '', 0, 0, 0, 0, 0, 0),
(5, 4, 4, 16, 16, '2025-10-28', '12:03:00', 'realizado', '', 0, 0, 0, 0, 0, 1),
(6, 4, 5, 18, 16, '2025-11-07', '15:00:00', 'realizado', 'Gostaria que ficasse, vindo sempre as sextas feiras.', 0, 0, 0, 0, 0, 1),
(7, 4, 4, 38, 16, '2025-10-31', '19:00:00', 'aceito', '', 0, 0, 0, 0, 0, 0),
(8, 5, 1, 31, 21, '2025-11-04', '11:00:00', 'realizado', 'Gostaria que vinhesse e realizasse a limpeza com todos os equipamentos da empresa.', 0, 0, 0, 0, 0, 1),
(9, 2, 5, 21, 20, '2025-10-30', '12:00:00', 'aceito', 'Tenho 2 cachorros, 2 crianças pequenas e não consigo tirá-las de casa. ', 0, 0, 0, 0, 0, 0),
(10, 4, 1, 15, 16, '2025-11-07', '12:00:00', 'aceito', '', 0, 0, 0, 0, 0, 0),
(11, 4, 1, 15, 22, '2025-11-11', '22:56:00', 'cancelado', 'teste', 0, 0, 0, 0, 0, 0),
(12, 4, 1, 25, 23, '2025-11-29', '15:00:00', 'aceito', '', 0, 0, 0, 0, 0, 0),
(13, 5, 1, 15, 21, '2025-11-26', '12:00:00', 'aceito', '', 0, 0, 0, 0, 0, 0),
(14, 5, 1, 15, 31, '2025-11-26', '12:01:00', 'aceito', '', 0, 0, 0, 0, 0, 0),
(15, 5, 1, 23, 31, '2025-11-29', '17:00:00', 'aceito', '', 1, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacao_prestador`
--

CREATE TABLE `avaliacao_prestador` (
  `id` int(11) NOT NULL,
  `Cliente_id` int(11) NOT NULL,
  `Prestador_id` int(11) NOT NULL,
  `nota` int(11) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `oculto` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `avaliacao_prestador`
--

INSERT INTO `avaliacao_prestador` (`id`, `Cliente_id`, `Prestador_id`, `nota`, `comentario`, `oculto`) VALUES
(1, 4, 4, 5, 'A limpeza mensal foi excelente, como sempre. A profissional foi muito eficiente e deixou tudo impecável, com atenção especial aos detalhes. O serviço contínuo é de alta qualidade e estou muito satisfeito. Obrigada Maria!', 0),
(2, 2, 1, 5, 'Contratei os serviços da Letícia Santos pela Starclean e fiquei extremamente satisfeita com o resultado. Ela foi pontual, atenciosa e demonstrou muito cuidado com cada detalhe da limpeza. O ambiente ficou impecável, com um aroma agradável e tudo organizado. Além disso, Letícia foi muito educada e profissional durante todo o atendimento. Recomendo fortemente o trabalho dela para quem busca excelência e confiança no serviço de limpeza.', 0),
(3, 4, 5, 5, 'Serviço realizado com maestria! Prestador muito cuidadoso, deixou todos os cantos da minha casa limpa.', 1);

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
(2, 'Allana Larissa', 'dos Santos', 'allanalarissa5@gmail.com', '2005-03-30', '(61)99181-7265', '077.469.001-19', '$2y$10$k3mmEMEj3zo3jPp4lt4WsOS99z8OZIdCRFgNvbS67FT.cH3.aJ6KG', 1, '2025-10-07 02:48:05', '2025-10-28 00:38:21'),
(4, 'Pedro Lucas dos Santos', 'Vieira', 'lucaspedro1030@gmail.com', '2008-10-30', '(61) 99402-4265', '077.469.451-39', '$2y$10$t9tXsVxBXOIQDxryciP.YujHQLpvbAOwIbZwlfG61XZFkUjs1Gj52', 1, '2025-10-18 01:21:02', '2025-10-18 01:21:02'),
(5, 'Francisco Vieira', 'da Silva', 'francisco@gmail.com', '1978-02-10', '(61) 99369-5893', '833.777.861-04', '$2y$10$RpeE/mQEcPnQPypU673Eg.9jvQQJom8duO/I5RUs6yuVShXSQp8WC', 1, '2025-10-27 08:56:15', '2025-10-27 08:56:15'),
(7, 'cinthia', 'REIS', 'ayllasantosdf@hotmail.com', '2000-01-01', '(99) 99999-9999', '017.202.861-24', '$2y$10$jI4GBcf3G/bjvfdT8I7iVedZh7/ZQRX4b6/aCPn8.xPEyZP2cSToW', 1, '2025-10-29 01:15:07', '2025-10-29 01:15:07'),
(8, 'Aylla', 'Leticia', 'aylla@gmail.com', '2025-11-06', '(61) 99999-9999', '627.489.573-69', '$2y$10$dYpEpk4Ei//lYK0CwRb5Dun1wiIg5GfqfWdZ2ecgRW6/drLoBTe7i', 1, '2025-10-29 01:17:16', '2025-10-29 01:17:16');

-- --------------------------------------------------------

--
-- Estrutura para tabela `conteudo_pagina_inicial`
--

CREATE TABLE `conteudo_pagina_inicial` (
  `id` int(11) NOT NULL,
  `tipo_conteudo` enum('carousel','card') NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `texto` text DEFAULT NULL,
  `imagem_url` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `texto_botao` varchar(100) DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_por_admin_id` int(11) DEFAULT NULL,
  `editado_por_admin_id` int(11) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_edicao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `conteudo_pagina_inicial`
--

INSERT INTO `conteudo_pagina_inicial` (`id`, `tipo_conteudo`, `titulo`, `texto`, `imagem_url`, `link_url`, `texto_botao`, `ordem`, `ativo`, `criado_por_admin_id`, `editado_por_admin_id`, `data_criacao`, `data_edicao`) VALUES
(1, 'carousel', 'Bem-vindos à StarClean', 'A sua plataforma para agendar serviços de limpeza com qualidade e confiança.', 'img/sliderbar_1.png', NULL, NULL, 1, 1, NULL, 1, '2025-11-05 09:28:24', '2025-11-05 09:29:44'),
(2, 'carousel', 'Seja um dos nossos Clientes', 'Encontre os melhores prestadores de serviços de limpeza.', 'img/sliderbar_1.png', 'pages/cadastro.php', 'Cadastre-se', 2, 1, NULL, 1, '2025-11-05 09:28:24', '2025-11-05 09:29:44'),
(3, 'carousel', 'Seja um Prestador de Serviços', 'Junte-se a nós e ofereça seus serviços de limpeza.', 'img/sliderbar_1.png', 'pages/cadastro.php', 'Cadastre-se', 3, 1, NULL, 1, '2025-11-05 09:28:24', '2025-11-05 09:29:44'),
(4, 'card', 'Higienização do Escritório', 'Este é um serviço de limpeza especializado para escritórios, garantindo um ambiente de trabalho limpo e saudável.', 'img/escritorio2.png', NULL, NULL, 1, 1, NULL, 1, '2025-11-05 09:28:24', '2025-11-05 09:29:44'),
(5, 'card', 'Limpeza completa da sua casa.', 'Este é um serviço de limpeza especializado para residencias, garantindo que a sua casa permaneça limpa e bem cuidada.', 'img/cozinha.png', NULL, NULL, 2, 1, NULL, 1, '2025-11-05 09:28:24', '2025-11-05 09:29:44'),
(6, 'card', 'Higienização da Biblioteca', 'Este é um serviço de limpeza especializado para bibliotecas, garantindo um ambiente de leitura limpo e organizado para sua familia.', 'img/biblioteca.png', NULL, NULL, 3, 1, NULL, 1, '2025-11-05 09:28:24', '2025-11-05 09:29:44'),
(7, 'card', 'Higienização da Sala', 'Este é um serviço de limpeza especializado para salas de estar, garantindo um ambiente aconchegante limpo e bem cuidado.', 'img/sala.png', NULL, NULL, 4, 1, NULL, 1, '2025-11-05 09:28:24', '2025-11-05 09:29:44'),
(8, 'card', 'Higienização do Quarto', 'Este é um serviço de limpeza especializado para casas no geral você pode contratar na diária, mensalmente e por pacotes.', 'img/quarto.png', NULL, NULL, 5, 1, NULL, 1, '2025-11-05 09:28:24', '2025-11-05 09:31:25'),
(9, 'card', 'Outros Serviços', 'Na Star Clean, oferecemos uma variedade de serviços adicionais para atender às suas necessidades específicas.', 'img/limpando.jpg', 'pages/servicos.php', 'Ver mais', 6, 1, NULL, 1, '2025-11-05 09:28:24', '2025-11-05 09:29:44');

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
  `latitude` decimal(10,8) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `endereco`
--

INSERT INTO `endereco` (`id`, `Cliente_id`, `Prestador_id`, `cep`, `logradouro`, `bairro`, `cidade`, `uf`, `numero`, `complemento`, `latitude`, `criado_em`, `atualizado_em`, `longitude`) VALUES
(16, 4, NULL, '72238-369', 'Quadra SHPS Quadra 603 Conjunto C', 'Setor Habitacional Pôr do Sol (Ceilândia)', 'Brasília', 'DF', '18', 'Conjunto C', NULL, '2025-10-21 00:40:48', '2025-11-04 16:45:47', NULL),
(19, 4, NULL, '72238360', 'Quadra SHPS Quadra 603', 'Setor Habitacional Pôr do Sol (Ceilândia)', 'Brasília', 'DF', '603', 'Conjunto C', NULL, '2025-10-21 22:43:18', '2025-10-21 22:43:18', NULL),
(20, 2, NULL, '72231117', 'Quadra QNP 10 Conjunto Q', 'Ceilândia Sul (Ceilândia)', 'Brasília', 'DF', '46', 'P.sul', NULL, '2025-10-23 02:29:54', '2025-11-05 16:09:39', NULL),
(21, 5, NULL, '72270-500', 'Quadra QNQ 5', 'Ceilândia Norte (Ceilândia)', 'Brasília', 'DF', '17', 'Casa', NULL, '2025-10-27 08:57:31', '2025-11-04 22:56:16', NULL),
(22, 4, NULL, '72270500', 'Quadra QNQ 5', 'Ceilândia Norte (Ceilândia)', 'Brasília', 'DF', '1', 'Casa somente', NULL, '2025-10-29 00:54:38', '2025-11-04 16:11:04', NULL),
(23, 4, NULL, '72270500', 'Quadra QNQ 5', 'Ceilândia Norte (Ceilândia)', 'Brasília', 'DF', '1', 'casa2', NULL, '2025-11-04 00:56:47', '2025-11-04 02:16:39', NULL),
(24, 4, NULL, '72316-116', 'Quadra QR 208 Conjunto 15', 'Samambaia Norte (Samambaia)', 'Brasília', 'DF', '6', '', 0.00000000, '2025-11-04 16:18:26', '2025-11-05 16:09:29', NULL),
(25, 5, NULL, '72238-369', 'Quadra SHPS Quadra 603 Conjunto C', 'Setor Habitacional Pôr do Sol (Ceilândia)', 'Brasília', 'DF', '18', 'Conjunto C', -15.85502000, '2025-11-04 23:28:47', '2025-11-04 23:28:47', -48.12433000),
(31, 5, NULL, '73369-012', 'Quadra 3L Conjunto B', 'Arapoanga (Planaltina)', 'Brasília', 'DF', '12', '', -15.64551901, '2025-11-05 00:39:09', '2025-11-05 00:39:09', -47.64884224);

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_atividades`
--

CREATE TABLE `log_atividades` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `acao` varchar(255) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `data_ocorrencia` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `log_atividades`
--

INSERT INTO `log_atividades` (`id`, `admin_id`, `acao`, `detalhes`, `data_ocorrencia`) VALUES
(1, 1, 'Editou o conteúdo da página inicial.', NULL, '2025-11-05 09:29:44'),
(2, 1, 'Editou o conteúdo da página inicial.', NULL, '2025-11-05 09:31:25');

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
(6, 'allanalarissa5@gmail.com', '323f4c7303abff7c770f7c965ecfac4a3d54e73393c986dbdfca27f79d26ce14bc520a3c01b31baf90e247140f0636614457', '2025-10-26 20:27:51', '2025-10-26 18:27:51'),
(14, 'starclean.prest.servicos@gmail.com', 'f644183bfec8534bd2e2fbb93401d57628aa8281bf17ec1ee1dd80ea343b97ffbb44020399c5b312fe897c4dd76f93d529c2', '2025-10-27 03:17:11', '2025-10-27 01:17:11'),
(29, 'ayllasantosdf@hotmail.com', '9701757cecc29fb4b9570f96748fdfba7122866ef2b0ef11be44cce3d8e3c9d79c16b792c61b5df0c57f80a1a5c428dd55b3', '2025-10-28 01:04:07', '2025-10-27 23:04:07'),
(34, 'lucaspedro1030@gmail.com', '9049db995f67454d21025d8dcabec1689dff7c01360fda037e67657373292788c78d073faf2fcbe16e041c20a6dc2bc35ffd', '2025-10-28 14:38:02', '2025-10-28 12:38:02'),
(36, 'jeleticiasantosdf@gmail.com', '0ed55abc8e9b3580b4e26590dd376c5c24c2c5a207ae765bfe8067b0242becd8334748e6d23a0a0e7f0fac2e8a7c42ce6a20', '2025-10-29 03:00:44', '2025-10-29 01:00:44');

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
(18, 'Combo 1 nível básico', 'Para casa de 3 quartos mensal', 1080, NULL, 5),
(21, 'Combo 1 nível básico', 'Para casa de +4 quartos diária', 450, NULL, 5),
(23, 'Combo 1 nível básico', 'Para escritório de 1 sala diária', 190, NULL, 1),
(25, 'Combo 1 nível básico', 'Para escritório de 3 salas diária', 260, NULL, 1),
(31, 'Combo 2 nível intermediário', 'Para casa de 2 quartos diária', 270, NULL, 1),
(38, 'Combo 2 nível intermediário', 'Para casa de +4 quartos diária', 500, NULL, 4),
(88, 'Combo 1 Nível Básico', 'para casa de 1 quarto diária', 180, NULL, 1),
(90, 'Combo 1 Nível Básico', 'para casa de 3 quartos diária', 270, NULL, 1),
(92, 'Combo 1 Nível Básico', 'para casa de 4 quartos diária', 320, NULL, 1),
(93, 'Combo 1 Nível Básico', 'para casa de 4 quartos mensal', 1280, NULL, 1),
(95, 'Combo 1 Nível Básico', 'para casa de +4 quartos mensal', 1800, NULL, 1),
(97, 'Combo 1 Nível Básico', 'para escritório de 1 sala mensal', 760, NULL, 1),
(99, 'Combo 1 Nível Básico', 'para escritório de 3 salas mensal', 1040, NULL, 1),
(100, 'Combo 1 Nível Básico', 'para escritório de 4 salas diária', 300, NULL, 1),
(101, 'Combo 1 Nível Básico', 'para escritório de 4 salas mensal', 1200, NULL, 1),
(102, 'Combo 1 Nível Básico', 'para escritório de +4 salas diária', 400, NULL, 1),
(103, 'Combo 1 Nível Básico', 'para escritório de +4 salas mensal', 1600, NULL, 1),
(105, 'Combo 2 Nível Intermediário', 'para casa de 2 quartos mensal', 1000, NULL, 4),
(106, 'Combo 2 Nível Intermediário', 'para casa de 3 quartos diária', 300, NULL, 4),
(107, 'Combo 2 Nível Intermediário', 'para casa de 3 quartos mensal', 1200, NULL, 4),
(108, 'Combo 2 Nível Intermediário', 'para casa de 4 quartos diária', 350, NULL, 4),
(109, 'Combo 2 Nível Intermediário', 'para casa de 4 quartos mensal', 1400, NULL, 4),
(111, 'Combo 2 Nível Intermediário', 'para casa de +4 quartos mensal', 2000, NULL, 4),
(112, 'Combo 2 Nível Intermediário', 'para escritório de 2 salas diária', 260, NULL, 4),
(113, 'Combo 2 Nível Intermediário', 'para escritório de 2 salas mensal', 1040, NULL, 4),
(114, 'Combo 2 Nível Intermediário', 'para escritório de 3 salas diária', 320, NULL, 4),
(115, 'Combo 2 Nível Intermediário', 'para escritório de 3 salas mensal', 1280, NULL, 4),
(116, 'Combo 2 Nível Intermediário', 'para escritório de 4 salas diária', 380, NULL, 4),
(117, 'Combo 2 Nível Intermediário', 'para escritório de 4 salas mensal', 1520, NULL, 4),
(118, 'Combo 2 Nível Intermediário', 'para escritório de +4 salas diária', 480, NULL, 4),
(119, 'Combo 2 Nível Intermediário', 'para escritório de +4 salas mensal', 1920, NULL, 4),
(120, 'Combo 3 Nível Brilhante', 'para casa de 1 quarto diária', 360, NULL, 5),
(121, 'Combo 3 Nível Brilhante', 'para casa de 1 quarto mensal', 1440, NULL, 5),
(122, 'Combo 3 Nível Brilhante', 'para casa de 2 quartos diária', 430, NULL, 5),
(123, 'Combo 3 Nível Brilhante', 'para casa de 2 quartos mensal', 1720, NULL, 5),
(124, 'Combo 3 Nível Brilhante', 'para casa de 3 quartos diária', 520, NULL, 5),
(125, 'Combo 3 Nível Brilhante', 'para casa de 3 quartos mensal', 2080, NULL, 5),
(126, 'Combo 3 Nível Brilhante', 'para casa de +4 quartos diária', 620, NULL, 5),
(127, 'Combo 3 Nível Brilhante', 'para casa de +4 quartos mensal', 2480, NULL, 5),
(128, 'Combo 3 Nível Brilhante', 'para escritório de 1 sala diária', 440, NULL, 5),
(129, 'Combo 3 Nível Brilhante', 'para escritório de 1 sala mensal', 1760, NULL, 5),
(130, 'Combo 3 Nível Brilhante', 'para escritório de 2 salas diária', 490, NULL, 5),
(131, 'Combo 3 Nível Brilhante', 'para escritório de 2 salas mensal', 1960, NULL, 5),
(132, 'Combo 3 Nível Brilhante', 'para escritório de 3 salas diária', 580, NULL, 5),
(133, 'Combo 3 Nível Brilhante', 'para escritório de 3 salas mensal', 2320, NULL, 5),
(134, 'Combo 3 Nível Brilhante', 'para escritório de +4 salas diária', 680, NULL, 5),
(135, 'Combo 3 Nível Brilhante', 'para escritório de +4 salas mensal', 2720, NULL, 5);

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
-- Índices de tabela `conteudo_pagina_inicial`
--
ALTER TABLE `conteudo_pagina_inicial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_conteudo_criado_por` (`criado_por_admin_id`),
  ADD KEY `fk_conteudo_editado_por` (`editado_por_admin_id`);

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
-- Índices de tabela `log_atividades`
--
ALTER TABLE `log_atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_atividades_admin_idx` (`admin_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `agendamento`
--
ALTER TABLE `agendamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `avaliacao_prestador`
--
ALTER TABLE `avaliacao_prestador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `avaliacao_servico`
--
ALTER TABLE `avaliacao_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `conteudo_pagina_inicial`
--
ALTER TABLE `conteudo_pagina_inicial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `disponibilidade`
--
ALTER TABLE `disponibilidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `endereco`
--
ALTER TABLE `endereco`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `log_atividades`
--
ALTER TABLE `log_atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `prestador`
--
ALTER TABLE `prestador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `redefinicao_senha`
--
ALTER TABLE `redefinicao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `servico`
--
ALTER TABLE `servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

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
-- Restrições para tabelas `conteudo_pagina_inicial`
--
ALTER TABLE `conteudo_pagina_inicial`
  ADD CONSTRAINT `fk_conteudo_criado_por` FOREIGN KEY (`criado_por_admin_id`) REFERENCES `administrador` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_conteudo_editado_por` FOREIGN KEY (`editado_por_admin_id`) REFERENCES `administrador` (`id`) ON DELETE SET NULL;

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
-- Restrições para tabelas `log_atividades`
--
ALTER TABLE `log_atividades`
  ADD CONSTRAINT `fk_log_atividades_admin` FOREIGN KEY (`admin_id`) REFERENCES `administrador` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
