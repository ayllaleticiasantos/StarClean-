-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09/11/2025 às 03:43
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
(2, 'StarClean', 'Serviços', 'starclean.prest.servicos@gmail.com', '$2y$10$qeEKq77HSawARhHTbWPGDO/3TJnMpn1js3WiCoU5yqQ9egcItvYMy', 'adminusuario', 1, '2025-10-06 23:07:13', '2025-11-02 22:32:28'),
(7, 'Cinthia Reis', 'Cirilo', 'desenvolvedor.iantec@gmail.com', '$2y$10$tjCoEUhwtcHE6fps4hfwSuYSDBTJ.xWRuCE0hbT/xHaFso.5iAzO6', 'adminusuario', 1, '2025-11-08 21:09:08', '2025-11-08 21:09:08');

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
(2, 4, 1, 15, 16, '2025-10-31', '13:00:00', 'remarcado', 'escritorio\r\n', 0, 0, 0, 0, 0, 0),
(3, 4, 1, 15, 16, '2025-10-30', '12:00:00', 'remarcado', '', 0, 0, 0, 0, 0, 0),
(5, 4, 4, 16, 16, '2025-10-28', '12:03:00', 'realizado', '', 0, 0, 0, 0, 0, 1),
(6, 4, 5, 18, 16, '2025-11-07', '15:00:00', 'realizado', 'Gostaria que ficasse, vindo sempre as sextas feiras.', 0, 0, 0, 0, 0, 1),
(7, 4, 4, 38, 16, '2025-10-31', '19:00:00', 'aceito', '', 0, 0, 0, 0, 1, 0),
(8, 5, 1, 31, 21, '2025-11-04', '11:00:00', 'realizado', 'Gostaria que vinhesse e realizasse a limpeza com todos os equipamentos da empresa.', 0, 0, 0, 0, 0, 1),
(9, 2, 5, 21, 20, '2025-11-30', '12:00:00', 'aceito', 'Tenho 2 cachorros, 2 crianças pequenas e não consigo tirá-las de casa. ', 0, 0, 0, 0, 1, 0),
(10, 4, 1, 15, 16, '2025-11-07', '12:00:00', 'realizado', '', 0, 0, 0, 0, 0, 0),
(12, 4, 1, 25, 23, '2025-11-29', '15:00:00', 'aceito', '', 0, 0, 0, 0, 1, 0),
(13, 5, 1, 15, 21, '2025-11-26', '12:00:00', 'aceito', '', 0, 0, 0, 0, 1, 0),
(14, 5, 1, 15, 31, '2025-11-26', '12:01:00', 'aceito', '', 0, 0, 0, 0, 1, 0),
(15, 5, 1, 23, 31, '2025-11-29', '17:00:00', 'cancelado', '', 1, 0, 0, 0, 1, 0),
(16, 5, 1, 25, 31, '2025-11-18', '12:00:00', 'aceito', '', 0, 0, 0, 0, 1, 0),
(17, 5, 1, 23, 31, '2025-11-14', '12:00:00', 'aceito', '', 1, 1, 0, 0, 1, 0),
(18, 5, 1, 15, 31, '2025-11-25', '11:00:00', 'cancelado', '', 0, 0, 0, 0, 0, 0),
(19, 5, 1, 15, 31, '2025-11-11', '13:00:00', 'aceito', '', 0, 0, 1, 1, 1, 0),
(20, 2, 1, 15, 33, '2025-11-28', '12:00:00', 'pendente', 'Meu escritorio é pequeno, são 2 salas e uma recepção.', 0, 0, 0, 1, 0, 0),
(21, 5, 1, 23, 31, '2025-11-27', '18:00:00', 'pendente', '', 0, 1, 0, 0, 0, 0),
(22, 4, 4, 16, 24, '2025-11-24', '16:30:00', 'pendente', '', 1, 1, 0, 0, 0, 0),
(23, 4, 1, 15, 24, '2025-11-11', '17:10:00', 'pendente', '', 0, 0, 1, 0, 0, 0),
(24, 4, 4, 16, 24, '2025-11-21', '14:25:00', 'pendente', '', 0, 0, 0, 0, 0, 0),
(25, 4, 1, 15, 24, '2025-11-27', '14:35:00', 'pendente', '', 0, 0, 0, 0, 0, 0),
(26, 4, 1, 15, 24, '2025-11-24', '09:15:00', 'pendente', '', 0, 0, 0, 0, 0, 0),
(27, 4, 1, 88, 24, '2025-11-19', '15:35:00', 'pendente', '', 1, 0, 1, 0, 0, 0);

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
(5, 'Francisco Vieira', 'da Silva', 'francisco@gmail.com', '1978-02-10', '(61) 99369-5893', '833.777.861-04', '$2y$10$RpeE/mQEcPnQPypU673Eg.9jvQQJom8duO/I5RUs6yuVShXSQp8WC', 1, '2025-10-27 08:56:15', '2025-10-27 08:56:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `conteudo_geral`
--

CREATE TABLE `conteudo_geral` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) NOT NULL COMMENT 'Identificador único do conteúdo (ex: index_hero_titulo)',
  `titulo` varchar(255) NOT NULL COMMENT 'Descrição amigável para o admin (ex: Título Principal da Home)',
  `conteudo` text DEFAULT NULL COMMENT 'O texto ou URL da imagem',
  `tipo` enum('texto_simples','textarea','imagem') NOT NULL DEFAULT 'texto_simples' COMMENT 'Define o tipo de campo no formulário',
  `pagina` varchar(50) NOT NULL COMMENT 'Página a que o conteúdo pertence (ex: index, sobre)',
  `criado_por_admin_id` int(11) DEFAULT NULL,
  `editado_por_admin_id` int(11) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_edicao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `conteudo_geral`
--

INSERT INTO `conteudo_geral` (`id`, `chave`, `titulo`, `conteudo`, `tipo`, `pagina`) VALUES
(1, 'index_hero_titulo', 'Título Principal (Hero)', 'Limpeza Completa e Personalizada', 'texto_simples', 'index'),
(2, 'index_hero_subtitulo', 'Subtítulo Principal (Hero)', 'Oferecemos serviços abrangentes de limpeza para empresas, condomínios e residências.\nTrabalhamos com combos ou planos personalizados para atender sua necessidade.', 'textarea', 'index'),
(3, 'index_diferenciais_titulo', 'Título da Seção \"Diferenciais\"', 'Por que escolher a Star Clean?', 'texto_simples', 'index'),
(4, 'index_diferenciais_card1_titulo', 'Diferencial 1: Título', 'Equipe Qualificada', 'texto_simples', 'index'),
(5, 'index_diferenciais_card1_texto', 'Diferencial 1: Texto', 'Nossos Prestadores de Serviços são capacitados e recebem cursos semestrais para garantir um serviço de excelência.', 'textarea', 'index'),
(6, 'index_diferenciais_card2_titulo', 'Diferencial 2: Título', 'Garantia de Qualidade', 'texto_simples', 'index'),
(7, 'index_diferenciais_card2_texto', 'Diferencial 2: Texto', 'Nosso diferencial! Após o serviço, <strong>é feita uma vistoria do trabalho prestado</strong> para garantir sua total satisfação.', 'textarea', 'index'),
(8, 'index_diferenciais_card3_titulo', 'Diferencial 3: Título', 'Preços e Mimos', 'texto_simples', 'index'),
(9, 'index_diferenciais_card3_texto', 'Diferencial 3: Texto', 'Oferecemos preços justos, <strong>5% de desconto na primeira compra</strong> e fragrâncias exclusivas para clientes fiéis.', 'textarea', 'index'),
(10, 'sobre_hero_titulo', 'Sobre: Título Principal', 'Sobre a StarClean', 'texto_simples', 'sobre'),
(11, 'sobre_hero_subtitulo', 'Sobre: Subtítulo', 'Conheça nossa jornada, nossos valores e o que nos move a cada dia.', 'textarea', 'sobre'),
(12, 'sobre_historia_titulo', 'Sobre: Título da História', 'Nossa História', 'texto_simples', 'sobre'),
(13, 'sobre_historia_texto', 'Sobre: Texto da História', 'Fundada em 2023, a StarClean nasceu da visão de transformar a maneira como os serviços de limpeza são percebidos e entregues. Começamos com uma pequena equipe dedicada e um grande sonho: oferecer um serviço confiável, de alta qualidade e acessível para residências e empresas em nossa comunidade.', 'textarea', 'sobre'),
(14, 'sobre_missao_titulo', 'Sobre: Título da Missão', 'Nossa Missão', 'texto_simples', 'sobre'),
(15, 'sobre_missao_texto', 'Sobre: Texto da Missão', 'Facilitar a vida dos nossos clientes, proporcionando ambientes limpos e organizados através de uma plataforma que conecta profissionais qualificados a quem precisa de um serviço de excelência, com segurança e praticidade.', 'textarea', 'sobre'),
(16, 'sobre_visao_titulo', 'Sobre: Título da Visão', 'Nossa Visão', 'texto_simples', 'sobre'),
(17, 'sobre_visao_texto', 'Sobre: Texto da Visão', 'Ser a plataforma de intermediação de serviços de limpeza mais confiável e recomendada do Distrito Federal, reconhecida pela qualidade de seus prestadores e pela satisfação de seus clientes.', 'textarea', 'sobre'),
(18, 'sobre_dados_titulo', 'Sobre: Título da Seção de Dados', 'Nossos Dados', 'texto_simples', 'sobre'),
(19, 'sobre_dados_endereco', 'Sobre: Endereço', 'Taguatinga centro - Quadra C 11 loja 2', 'texto_simples', 'sobre'),
(20, 'sobre_dados_telefones', 'Sobre: Telefones', '(61) 99145-8746 / (61) 3150-9671', 'texto_simples', 'sobre'),
(21, 'sobre_dados_email', 'Sobre: Email', 'starclean.prest.servicos@gmail.com', 'texto_simples', 'sobre'),
(22, 'sobre_dados_horario', 'Sobre: Horário de Atendimento', 'Segunda a sexta (8h às 18h) e Sábados (9h às 15h)', 'texto_simples', 'sobre'),
(23, 'termos_de_uso_conteudo', 'Conteúdo Completo dos Termos de Uso', '<h2 dir=\"ltr\">Termos e Condi&ccedil;&otilde;es de Uso &ndash; StarClean</h2>\r\n<p dir=\"ltr\">Data da &Uacute;ltima Atualiza&ccedil;&atilde;o: 10 de novembro de 2025.</p>\r\n<p dir=\"ltr\">Bem-vindo(a) a StarClean!</p>\r\n<p dir=\"ltr\">Estes Termos e Condi&ccedil;&otilde;es de Uso regem o acesso e a utiliza&ccedil;&atilde;o do sistema da StarClean, operada por Star Clean&nbsp; , CNPJ 01.123.567/0001-00. Ao se cadastrar e utilizar nossa Plataforma, voc&ecirc; Usu&aacute;rio concorda integralmente com as condi&ccedil;&otilde;es aqui estabelecidas.</p>\r\n<p dir=\"ltr\">Recomendamos a leitura atenta deste documento.</p>\r\n<h3 dir=\"ltr\">1. Defini&ccedil;&otilde;es Principais</h3>\r\n<p dir=\"ltr\">Para os fins destes Termos, as seguintes defini&ccedil;&otilde;es ser&atilde;o adotadas:</p>\r\n<ul>\r\n<li dir=\"ltr\" aria-level=\"1\">\r\n<p dir=\"ltr\" role=\"presentation\"><strong>Plataforma/Sistema:</strong> O sistema web e/ou m&oacute;vel \"StarClean\", que atua como intermediador, conectando Clientes e Prestadores de servi&ccedil;os.</p>\r\n</li>\r\n<li dir=\"ltr\" aria-level=\"1\">\r\n<p dir=\"ltr\" role=\"presentation\"><strong>Usu&aacute;rio</strong>: Qualquer pessoa f&iacute;sica ou jur&iacute;dica que utilize a Plataforma, podendo ser classificada como Cliente ou Prestador.</p>\r\n</li>\r\n<li dir=\"ltr\" aria-level=\"1\">\r\n<p dir=\"ltr\" role=\"presentation\"><strong>Cliente: </strong>Usu&aacute;rio que se cadastra na Plataforma com o objetivo de contratar servi&ccedil;os.</p>\r\n</li>\r\n<li dir=\"ltr\" aria-level=\"1\">\r\n<p dir=\"ltr\" role=\"presentation\"><strong>Prestador: </strong>Usu&aacute;rio que se cadastra na Plataforma com o objetivo de realizar servi&ccedil;os.</p>\r\n</li>\r\n<li dir=\"ltr\" aria-level=\"1\">\r\n<p dir=\"ltr\" role=\"presentation\"><strong>Administrador:</strong> A equipe de gest&atilde;o da StarClean, respons&aacute;vel pela opera&ccedil;&atilde;o, manuten&ccedil;&atilde;o e gerenciamento dos dados da Plataforma.</p>\r\n</li>\r\n<li dir=\"ltr\" aria-level=\"1\">\r\n<p dir=\"ltr\" role=\"presentation\"><strong>LGPD: </strong>Lei Geral de Prote&ccedil;&atilde;o de Dados (Lei n&ordm; 13.709/2018).</p>\r\n</li>\r\n<li dir=\"ltr\" aria-level=\"1\">\r\n<p dir=\"ltr\" role=\"presentation\"><strong>CDC:</strong> C&oacute;digo de Defesa do Consumidor (Lei n&ordm; 8.078/1990).</p>\r\n</li>\r\n</ul>\r\n<h3 dir=\"ltr\">2. Objeto da Plataforma</h3>\r\n<p dir=\"ltr\">2.1. A <strong>StarClean </strong>&eacute; uma plataforma de tecnologia que atua como intermediadora, conectando <strong>Clientes </strong>que buscam servi&ccedil;os de limpeza a <strong>Prestadores </strong>de servi&ccedil;os qualificados e cadastrados.</p>\r\n<p dir=\"ltr\">2.2. A <strong>StarClean </strong>n&atilde;o &eacute; fornecedora direta dos servi&ccedil;os, atuando apenas como facilitadora da contrata&ccedil;&atilde;o e do gerenciamento dos agendamentos, al&eacute;m de possuir os utens&iacute;lios para que os <strong>Prestadores </strong>realizem o servi&ccedil;o de qualidade.</p>\r\n<h3 dir=\"ltr\">3. Cadastro e Elegibilidade</h3>\r\n<p dir=\"ltr\">3.1. Para utilizar a <strong>Plataforma</strong>, o <strong>Usu&aacute;rio </strong>deve ter no m&iacute;nimo 18 (dezoito) anos de idade e possuir capacidade legal para celebrar contratos.</p>\r\n<p dir=\"ltr\">3.2. O&nbsp;<strong>Usu&aacute;rio </strong>se compromete a fornecer informa&ccedil;&otilde;es verdadeiras, exatas, atualizadas e completas durante o cadastro e a manter seus dados atualizados.</p>\r\n<p dir=\"ltr\">3.3. A senha de acesso &eacute; pessoal, intransfer&iacute;vel e de responsabilidade exclusiva do&nbsp;<strong>Usu&aacute;rio</strong>, que deve mant&ecirc;-la em seguran&ccedil;a.</p>\r\n<h3 dir=\"ltr\">4. Privacidade e Prote&ccedil;&atilde;o de Dados (LGPD)</h3>\r\n<p dir=\"ltr\">A <strong>StarClean</strong> leva a s&eacute;rio a privacidade dos seus <strong>Usu&aacute;rios</strong> e segue rigorosamente a <strong>LGPD</strong>.</p>\r\n<p dir=\"ltr\">4.1. Consentimento e Coleta: Ao aceitar estes Termos, o <strong>Usu&aacute;rio </strong>fornece seu consentimento para a coleta e tratamento dos dados. A <strong>Plataforma </strong>coleta apenas os dados pessoais essenciais (Art. 5&ordm;, I) e, se aplic&aacute;vel, sens&iacute;veis (Art. 5&ordm;, II) de Clientes e Prestadores, estritamente necess&aacute;rios para a opera&ccedil;&atilde;o do <strong>Sistema </strong>e a presta&ccedil;&atilde;o dos servi&ccedil;os.</p>\r\n<p dir=\"ltr\">4.2. Finalidade e Compartilhamento (Art. 5&ordm;, V, VI, VII): Os dados pessoais dos&nbsp;<strong>Clientes </strong>ser&atilde;o acessados pelo <strong>Administrador </strong>e compartilhados com os <strong>Prestadores </strong>com a finalidade exclusiva de viabilizar o agendamento e a realiza&ccedil;&atilde;o do servi&ccedil;o contratado.</p>\r\n<p dir=\"ltr\">4.3. Consentimento Espec&iacute;fico (Art. 5&ordm;, XVI): O&nbsp;<strong>Cliente </strong>entende e consente que toda e qualquer tratativa dos dados fornecidos (coleta, armazenamento, compartilhamento com o <strong>Prestador</strong>, tratamento) &eacute; condi&ccedil;&atilde;o essencial e pertinente &agrave; presta&ccedil;&atilde;o dos servi&ccedil;os oferecidos pela <strong>StarClean</strong>.</p>\r\n<p dir=\"ltr\">4.4. Seguran&ccedil;a e Armazenamento (Art. 5&ordm;, IV): Todos os dados pessoais coletados s&atilde;o armazenados em um banco de dados criptografado, seguindo as melhores pr&aacute;ticas de seguran&ccedil;a da informa&ccedil;&atilde;o para proteger os <strong>Usu&aacute;rios</strong>.</p>\r\n<p dir=\"ltr\">4.5. Confidencialidade (Art. 5&ordm;, XVII): Todos os <strong>Prestadores</strong>, funcion&aacute;rios e parceiros envolvidos no tratamento de dados est&atilde;o cientes e vinculados por obriga&ccedil;&otilde;es contratuais de manter o sigilo absoluto sobre todas as informa&ccedil;&otilde;es pessoais e sens&iacute;veis &agrave;s quais tenham acesso.&nbsp;</p>\r\n<p dir=\"ltr\">4.6. Direitos do Titular (Art. 5&ordm;, X, XI, XII, XIV): O <strong>Usu&aacute;rio </strong>(titular dos dados) tem o direito de:&nbsp;</p>\r\n<p>&nbsp; &nbsp; &nbsp;4.6.1. Acessar e corrigir seus dados a qualquer momento atrav&eacute;s da Plataforma.</p>\r\n<p>&nbsp; &nbsp; &nbsp;4.6.2. Solicitar a elimina&ccedil;&atilde;o de seus dados dos nossos bancos de dados, o que implicar&aacute; no encerramento de sua conta, ressalvadas as hip&oacute;teses legais de guarda obrigat&oacute;ria.&nbsp;</p>\r\n<p>&nbsp; &nbsp; &nbsp;4.6.3. Revogar seu consentimento, ciente de que isso pode impossibilitar a continuidade do uso da <strong>Plataforma</strong>.</p>\r\n<p>4.7. Avalia&ccedil;&atilde;o An&ocirc;nima (Art. 5&ordm;, III): A <strong>Plataforma </strong>garante ao <strong>Cliente </strong>o direito de registrar avalia&ccedil;&otilde;es an&ocirc;nimas sobre os <strong>Prestadores </strong>e os servi&ccedil;os contratados, visando a melhoria cont&iacute;nua da qualidade.</p>\r\n<h3 dir=\"ltr\">5. Direitos e Deveres do Consumidor (CDC)</h3>\r\n<p dir=\"ltr\">A <strong>StarClean </strong>respeita o C&oacute;digo de Defesa do Consumidor e se pauta pela transpar&ecirc;ncia e qualidade.</p>\r\n<p dir=\"ltr\">5.1. Informa&ccedil;&atilde;o Clara (Art. 6&ordm;, III, XIII): Todos os servi&ccedil;os oferecidos pela Plataforma s&atilde;o descritos de forma clara, completa e precisa. O <strong>Sistema </strong>informa de maneira transparente os produtos/servi&ccedil;os dispon&iacute;veis, os valores e a disponibilidade dos <strong>Prestadores</strong>.</p>\r\n<p dir=\"ltr\">5.2. Interface (Art. 6&ordm;, II): O <strong>Sistema </strong>possui uma interface intuitiva que visa facilitar o acesso e a utiliza&ccedil;&atilde;o de todas as suas funcionalidades pelo <strong>Cliente</strong>.</p>\r\n<p dir=\"ltr\">5.3. Confiabilidade e Qualidade (Art. 6&ordm;, I): A <strong>StarClean </strong>adota crit&eacute;rios rigorosos de sele&ccedil;&atilde;o e verifica&ccedil;&atilde;o para o cadastro de <strong>Prestadores</strong>, visando garantir a confiabilidade, seguran&ccedil;a e qualidade dos servi&ccedil;os intermediados.</p>\r\n<p dir=\"ltr\">5.4. Prote&ccedil;&atilde;o Contra Publicidade Enganosa (Art. 6&ordm;, IV): A <strong>StarClean </strong>assegura que todas as informa&ccedil;&otilde;es anunciadas na <strong>Plataforma </strong>s&atilde;o ver&iacute;dicas. O <strong>Cliente </strong>est&aacute; protegido contra pr&aacute;ticas de propaganda enganosa ou abusiva.</p>\r\n<p dir=\"ltr\">5.5. Seguran&ccedil;a nas Transa&ccedil;&otilde;es (Art. 6&ordm;, IV): A <strong>Plataforma </strong>mant&eacute;m um registro seguro de todas as transa&ccedil;&otilde;es e agendamentos, visando evitar cobran&ccedil;as indevidas e garantir a rastreabilidade.</p>\r\n<h3 dir=\"ltr\">6. Regras da Presta&ccedil;&atilde;o de Servi&ccedil;o</h3>\r\n<p dir=\"ltr\">6.1. Escopo do Contrato (Art. 6&ordm;, V): O servi&ccedil;o contratado limita-se estritamente ao que foi agendado e especificado atrav&eacute;s da <strong>Plataforma</strong>. O <strong>Cliente </strong>n&atilde;o deve solicitar ao <strong>Prestador</strong>, durante a execu&ccedil;&atilde;o, a inclus&atilde;o de servi&ccedil;os ou tarefas extras que n&atilde;o foram previamente contratados. Tais solicita&ccedil;&otilde;es fogem do escopo contratual e da cobertura da <strong>Plataforma</strong>.</p>\r\n<p dir=\"ltr\">6.2. Preven&ccedil;&atilde;o de Danos (Art. 6&ordm;, VI): A <strong>StarClean </strong>trabalha ativamente para prevenir qualquer dano ao <strong>Cliente</strong>, seja atrav&eacute;s da sele&ccedil;&atilde;o de <strong>Prestadores </strong>ou dos mecanismos de seguran&ccedil;a da <strong>Plataforma</strong>.</p>\r\n<p dir=\"ltr\">6.3. Coopera&ccedil;&atilde;o Judici&aacute;ria (Art. 6&ordm;, VII): Em caso de eventual dano sofrido pelo <strong>Cliente</strong>, a <strong>StarClean </strong>n&atilde;o se op&otilde;e a colaborar com o Judici&aacute;rio, fornecendo todas as provas e registros transacionais dispon&iacute;veis em seu <strong>Sistema </strong>para a apura&ccedil;&atilde;o dos fatos, respeitando os limites da <strong>LGPD</strong>.</p>\r\n<h3 dir=\"ltr\">7. Limita&ccedil;&atilde;o de Responsabilidade</h3>\r\n<p dir=\"ltr\">7.1. A <strong>StarClean </strong>atua exclusivamente como intermediadora. N&atilde;o h&aacute; v&iacute;nculo empregat&iacute;cio (CLT) ou de subordina&ccedil;&atilde;o entre a <strong>StarClean </strong>e os <strong>Prestadores</strong>.</p>\r\n<p dir=\"ltr\">7.2. A responsabilidade pela execu&ccedil;&atilde;o correta, segura e pontual do servi&ccedil;o &eacute; do <strong>Prestador</strong>. A <strong>StarClean </strong>fornece a <strong>Plataforma </strong>para a conex&atilde;o e o gerenciamento, al&eacute;m do suporte e dos crit&eacute;rios de sele&ccedil;&atilde;o.</p>\r\n<p dir=\"ltr\">7.3. O <strong>Cliente </strong>&eacute; respons&aacute;vel por garantir que o ambiente para a presta&ccedil;&atilde;o do servi&ccedil;o seja seguro e acess&iacute;vel ao <strong>Prestador</strong>.</p>\r\n<h3 dir=\"ltr\">8. Altera&ccedil;&otilde;es dos Termos de Uso</h3>\r\n<p dir=\"ltr\">A <strong>StarClean </strong>se reserva o direito de modificar estes Termos a qualquer momento. Os <strong>Usu&aacute;rios </strong>ser&atilde;o notificados sobre altera&ccedil;&otilde;es significativas atrav&eacute;s da <strong>Plataforma </strong>ou por e-mail. O uso cont&iacute;nuo da <strong>Plataforma </strong>ap&oacute;s a notifica&ccedil;&atilde;o constitui aceita&ccedil;&atilde;o das novas condi&ccedil;&otilde;es.</p>\r\n<h3 dir=\"ltr\">9. Rescis&atilde;o</h3>\r\n<p dir=\"ltr\">9.1. O <strong>Usu&aacute;rio </strong>pode solicitar o encerramento de sua conta a qualquer momento, desde que n&atilde;o haja obriga&ccedil;&otilde;es pendentes (ex: pagamentos ou servi&ccedil;os agendados).</p>\r\n<p dir=\"ltr\">9.2. A <strong>StarClean </strong>pode suspender ou encerrar a conta de qualquer <strong>Usu&aacute;rio </strong>que viole estes Termos, utilize a plataforma de m&aacute;-f&eacute; ou infrinja a legisla&ccedil;&atilde;o aplic&aacute;vel.</p>\r\n<h3 dir=\"ltr\">10. Foro</h3>\r\n<p dir=\"ltr\">Para a resolu&ccedil;&atilde;o de quaisquer conflitos oriundos destes Termos, fica eleito o foro da comarca de Bras&iacute;lia-DF, Brasil, com ren&uacute;ncia expressa a qualquer outro, por mais privilegiado que seja.</p>\r\n<h3 dir=\"ltr\">11. Contato</h3>\r\n<p dir=\"ltr\">Em caso de d&uacute;vidas sobre estes Termos de Uso, entre em contato conosco atrav&eacute;s do e-mail: <a href=\"mailto:starclean.prest.servicos@gmail.com\">starclean.prest.servicos@gmail.com</a> .</p>\r\n<h2>&nbsp;</h2>', 'textarea', 'termos_de_uso');

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
(1, 'carousel', 'Bem-vindos à StarClean', 'A sua plataforma para agendar serviços de limpeza com qualidade e confiança.', 'img/Sliderbar_1.png', NULL, NULL, 1, 1, NULL, 1, '2025-11-05 09:28:24', '2025-11-05 23:59:14'),
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
(20, 2, NULL, '72231-117', 'Quadra QNP 10 Conjunto Q', 'Ceilândia Sul (Ceilândia)', 'Brasília', 'DF', '46', 'P.sul', NULL, '2025-10-23 02:29:54', '2025-11-08 20:27:20', NULL),
(21, 5, NULL, '72270-500', 'Quadra QNQ 5', 'Ceilândia Norte (Ceilândia)', 'Brasília', 'DF', '17', 'Casa', NULL, '2025-10-27 08:57:31', '2025-11-04 22:56:16', NULL),
(22, 4, NULL, '72270500', 'Quadra QNQ 5', 'Ceilândia Norte (Ceilândia)', 'Brasília', 'DF', '1', 'Casa somente', NULL, '2025-10-29 00:54:38', '2025-11-04 16:11:04', NULL),
(23, 4, NULL, '72270500', 'Quadra QNQ 5', 'Ceilândia Norte (Ceilândia)', 'Brasília', 'DF', '1', 'casa2', NULL, '2025-11-04 00:56:47', '2025-11-04 02:16:39', NULL),
(24, 4, NULL, '72316-116', 'Quadra QR 208 Conjunto 15', 'Samambaia Norte (Samambaia)', 'Brasília', 'DF', '6', '', 0.00000000, '2025-11-04 16:18:26', '2025-11-05 16:09:29', NULL),
(25, 5, NULL, '72238-369', 'Quadra SHPS Quadra 603 Conjunto C', 'Setor Habitacional Pôr do Sol (Ceilândia)', 'Brasília', 'DF', '18', 'Conjunto C', -15.85502000, '2025-11-04 23:28:47', '2025-11-04 23:28:47', -48.12433000),
(31, 5, NULL, '73369-012', 'Quadra 3L Conjunto B', 'Arapoanga (Planaltina)', 'Brasília', 'DF', '12', '', -15.64551901, '2025-11-05 00:39:09', '2025-11-05 00:39:09', -47.64884224),
(32, 2, NULL, '72660-136', 'Quadra 509 Conjunto 9', 'Recanto das Emas', 'Brasília', 'DF', '18', 'Apartamento', -15.64078722, '2025-11-08 20:26:27', '2025-11-08 20:26:27', -47.80252216),
(33, 2, NULL, '72231-117', 'Quadra QNP 10 Conjunto Q', 'Ceilândia Sul (Ceilândia)', 'Brasília', 'DF', '48', 'Casa', -15.83635752, '2025-11-08 20:31:56', '2025-11-08 20:31:56', -48.11734346);

-- --------------------------------------------------------

--
-- Estrutura para tabela `indisponibilidade_prestador`
--

CREATE TABLE `indisponibilidade_prestador` (
  `id` int(11) NOT NULL,
  `prestador_id` int(11) NOT NULL,
  `data_indisponivel` date NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `indisponibilidade_prestador`
--

INSERT INTO `indisponibilidade_prestador` (`id`, `prestador_id`, `data_indisponivel`, `criado_em`) VALUES
(1, 1, '2025-11-09', '2025-11-05 23:03:49'),
(2, 1, '2025-11-15', '2025-11-05 23:03:59'),
(3, 1, '2025-11-16', '2025-11-05 23:04:03'),
(4, 1, '2025-11-22', '2025-11-05 23:04:11'),
(5, 1, '2025-11-08', '2025-11-05 23:04:23'),
(6, 1, '2025-11-23', '2025-11-05 23:04:32'),
(7, 1, '2025-11-29', '2025-11-05 23:04:36'),
(8, 1, '2025-11-30', '2025-11-05 23:04:41'),
(11, 1, '2025-11-20', '2025-11-05 23:05:24'),
(13, 1, '2025-11-10', '2025-11-07 17:05:57');

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
(2, 1, 'Editou o conteúdo da página inicial.', NULL, '2025-11-05 09:31:25'),
(3, 1, 'Editou o conteúdo da página inicial.', NULL, '2025-11-05 16:30:13'),
(4, 1, 'Editou o conteúdo da página inicial.', NULL, '2025-11-05 23:22:45'),
(5, 1, 'Editou o conteúdo da página inicial.', NULL, '2025-11-05 23:58:38'),
(6, 1, 'Editou o conteúdo da página inicial.', NULL, '2025-11-05 23:59:14'),
(7, 1, 'Editou o conteúdo da página inicial.', NULL, '2025-11-07 23:41:35'),
(8, 1, 'Editou o conteúdo da página inicial.', NULL, '2025-11-07 23:44:12'),
(9, 1, 'Editou o conteúdo da página inicial.', NULL, '2025-11-07 23:48:13'),
(10, 1, 'Editou o conteúdo das páginas (inicial/sobre).', NULL, '2025-11-07 23:55:05'),
(11, 1, 'Editou o conteúdo das páginas (inicial/sobre).', NULL, '2025-11-07 23:55:28'),
(12, 1, 'Editou o conteúdo das páginas (inicial/sobre).', NULL, '2025-11-08 09:19:03'),
(13, 1, 'Editou o conteúdo das páginas (inicial/sobre).', NULL, '2025-11-08 09:37:42'),
(14, 1, 'Editou o conteúdo das páginas (inicial/sobre).', NULL, '2025-11-08 09:39:40'),
(15, 1, 'Editou o conteúdo das páginas (inicial/sobre).', NULL, '2025-11-08 09:41:06'),
(16, 1, 'Aceitou o agendamento ID: 19', '{\"agendamento_id\":\"19\"}', '2025-11-08 21:23:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_cliente_atividades`
--

CREATE TABLE `log_cliente_atividades` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `acao` varchar(255) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `data_ocorrencia` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_prestador_atividades`
--

CREATE TABLE `log_prestador_atividades` (
  `id` int(11) NOT NULL,
  `prestador_id` int(11) NOT NULL,
  `acao` varchar(255) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `data_ocorrencia` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Leticia Santos', 'Vieira', '071.818.111-50', 'jeleticiasantosdf@gmail.com', '6130428546', 'Limpeza de Ambientes Pequenos', '$2y$10$WJL/zSXipVh531YsCrGSEekJzq/oTKgdWW.yHk8skjYWFG8VYuBRW', 1, 'Profissional do ramo de limpeza de casas, pequenas e minimalistas desde o ano de 2016, com foco em impermeabilização garantindo que sua casa fique mais limpa, por mais tempo.', '2025-10-07 02:24:18', '2025-11-08 21:12:54', 1),
(4, 'Maria Francivânia dos Santos', 'Rocha', '807.991.401-04', 'vaniasantosrocha@outlook.com', '(61) 99345-8309', 'Limpeza de Ambientes Pequenos', '$2y$10$KI3LXORf.8XxRrQJ9o3lz.5l5DfBv80/tSZpzgQB4dh74kcl.awvS', 1, 'Sou profissional da área da limpeza a mais de 20 anos, trabalho normalmente em casas de familia. Estou buscando a StarClean Pela oportunidade de me conectar facilmente com a carta de clientes da empresa e poder usufruir dos equipamentos.', '2025-10-23 03:39:21', '2025-10-23 03:39:21', 1),
(5, 'Arthur Luís', 'dos Santos Vieira', '091.191.781-07', 'arthur@gmail.com', '(61) 99189-2912', 'Limpeza de Casas', '$2y$10$G4XGYvwR4xdVo7U5FQEJN./NQwlJgK1jkBIPGyO13CqpLjmgAKvDW', 1, 'Sou profissional da área da Limpeza, há 8 meses e busco na StarClean uma cartela de clientes vasta.', '2025-10-25 22:56:55', '2025-11-08 21:12:05', 1);

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
(34, 'lucaspedro1030@gmail.com', '9049db995f67454d21025d8dcabec1689dff7c01360fda037e67657373292788c78d073faf2fcbe16e041c20a6dc2bc35ffd', '2025-10-28 14:38:02', '2025-10-28 12:38:02'),
(39, 'ayllasantosdf@hotmail.com', '6a13e85f57a4a85172c396944acecb4c47bd214e4c7e920d3c0d76e38103b4123fc53a6c5dbf36e0af73994c5e4c6c8179e0', '2025-11-08 02:18:02', '2025-11-08 00:18:02'),
(40, 'jeleticiasantosdf@gmail.com', 'b3d0218fa033bdd087c0707845f2285213f5bba0466457c81c01928f29e8b3dcb9c1840350ca38e04d6b5742318669cfb61b', '2025-11-08 02:23:19', '2025-11-08 00:23:19'),
(43, 'desenvolvedor.iantec@gmail.com', '2d2488aef864fbe7d6cc6026a5567c78497474943001e3ccd7ee1561e726784a9c44880f9e4f9af7df5d591cf395e851f78b', '2025-11-09 01:40:06', '2025-11-08 23:40:06');

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
  `prestador_id` int(11) NOT NULL,
  `oculto` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = visível, 1 = oculto'
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
-- Índices de tabela `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf_UNIQUE` (`cpf`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- Índices de tabela `conteudo_geral`
--
ALTER TABLE `conteudo_geral`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`),
  ADD KEY `fk_conteudo_geral_criado_idx` (`criado_por_admin_id`),
  ADD KEY `fk_conteudo_geral_editado_idx` (`editado_por_admin_id`);

--
-- Índices de tabela `conteudo_pagina_inicial`
--
ALTER TABLE `conteudo_pagina_inicial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_conteudo_criado_por` (`criado_por_admin_id`),
  ADD KEY `fk_conteudo_editado_por` (`editado_por_admin_id`);

--
-- Índices de tabela `endereco`
--
ALTER TABLE `endereco`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Endereco_Cliente_idx` (`Cliente_id`),
  ADD KEY `fk_Endereco_Prestador1_idx` (`Prestador_id`);

--
-- Índices de tabela `indisponibilidade_prestador`
--
ALTER TABLE `indisponibilidade_prestador`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_prestador_data` (`prestador_id`,`data_indisponivel`);

--
-- Índices de tabela `log_atividades`
--
ALTER TABLE `log_atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_atividades_admin_idx` (`admin_id`);

--
-- Índices de tabela `log_cliente_atividades`
--
ALTER TABLE `log_cliente_atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_cliente_idx` (`cliente_id`);

--
-- Índices de tabela `log_prestador_atividades`
--
ALTER TABLE `log_prestador_atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_prestador_idx` (`prestador_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `agendamento`
--
ALTER TABLE `agendamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `avaliacao_prestador`
--
ALTER TABLE `avaliacao_prestador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `conteudo_geral`
--
ALTER TABLE `conteudo_geral`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `conteudo_pagina_inicial`
--
ALTER TABLE `conteudo_pagina_inicial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `endereco`
--
ALTER TABLE `endereco`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de tabela `indisponibilidade_prestador`
--
ALTER TABLE `indisponibilidade_prestador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `log_atividades`
--
ALTER TABLE `log_atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `log_cliente_atividades`
--
ALTER TABLE `log_cliente_atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `log_prestador_atividades`
--
ALTER TABLE `log_prestador_atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prestador`
--
ALTER TABLE `prestador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `redefinicao_senha`
--
ALTER TABLE `redefinicao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

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
-- Restrições para tabelas `conteudo_geral`
--
ALTER TABLE `conteudo_geral`
  ADD CONSTRAINT `fk_conteudo_geral_criado` FOREIGN KEY (`criado_por_admin_id`) REFERENCES `administrador` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_conteudo_geral_editado` FOREIGN KEY (`editado_por_admin_id`) REFERENCES `administrador` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `conteudo_pagina_inicial`
--
ALTER TABLE `conteudo_pagina_inicial`
  ADD CONSTRAINT `fk_conteudo_criado_por` FOREIGN KEY (`criado_por_admin_id`) REFERENCES `administrador` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_conteudo_editado_por` FOREIGN KEY (`editado_por_admin_id`) REFERENCES `administrador` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `endereco`
--
ALTER TABLE `endereco`
  ADD CONSTRAINT `fk_Endereco_Cliente` FOREIGN KEY (`Cliente_id`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Endereco_Prestador1` FOREIGN KEY (`Prestador_id`) REFERENCES `prestador` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `indisponibilidade_prestador`
--
ALTER TABLE `indisponibilidade_prestador`
  ADD CONSTRAINT `indisponibilidade_prestador_ibfk_1` FOREIGN KEY (`prestador_id`) REFERENCES `prestador` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `log_atividades`
--
ALTER TABLE `log_atividades`
  ADD CONSTRAINT `fk_log_atividades_admin` FOREIGN KEY (`admin_id`) REFERENCES `administrador` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `log_cliente_atividades`
--
ALTER TABLE `log_cliente_atividades`
  ADD CONSTRAINT `fk_log_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `log_prestador_atividades`
--
ALTER TABLE `log_prestador_atividades`
  ADD CONSTRAINT `fk_log_prestador` FOREIGN KEY (`prestador_id`) REFERENCES `prestador` (`id`) ON DELETE CASCADE;

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
