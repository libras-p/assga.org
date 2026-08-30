-- ============================================================
-- BANCO DE DADOS PARA ASSGA - ASSOCIAÇÃO DESPORTIVA
-- Compatível com MySQL / MariaDB
-- ============================================================

-- Cria o banco de dados (se não existir)
CREATE DATABASE IF NOT EXISTS assga_db 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco
USE assga_db;

-- ============================================================
-- TABELA: socios
-- ============================================================
CREATE TABLE IF NOT EXISTS socios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    nascimento VARCHAR(20),
    senha VARCHAR(255) NOT NULL,
    matricula VARCHAR(50),
    secretaria VARCHAR(100),
    foto LONGTEXT,
    meses_pagos JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: diretoria
-- ============================================================
CREATE TABLE IF NOT EXISTS diretoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    descricao TEXT,
    email VARCHAR(255),
    telefone VARCHAR(20),
    foto LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: noticias
-- ============================================================
CREATE TABLE IF NOT EXISTS noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    data VARCHAR(100),
    resumo TEXT,
    texto TEXT,
    imagem LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: historia
-- ============================================================
CREATE TABLE IF NOT EXISTS historia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    subtitulo VARCHAR(255),
    imagem LONGTEXT,
    texto_extra TEXT,
    fundacao TEXT,
    missao TEXT,
    valores TEXT,
    timeline JSON,
    numeros JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: esportiva
-- ============================================================
CREATE TABLE IF NOT EXISTS esportiva (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    subtitulo VARCHAR(255),
    imagem LONGTEXT,
    data_destaque VARCHAR(100),
    texto_extra TEXT,
    modalidades JSON,
    campeonatos JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: esportivas (itens com limite de 5)
-- ============================================================
CREATE TABLE IF NOT EXISTS esportivas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    data VARCHAR(100),
    texto TEXT,
    imagem LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: eventos
-- ============================================================
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    data VARCHAR(100),
    local VARCHAR(255),
    preco DECIMAL(10,2),
    status ENUM('aberto', 'andamento', 'fechado') DEFAULT 'aberto',
    imagem LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: estatuto
-- ============================================================
CREATE TABLE IF NOT EXISTS estatuto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    subtitulo VARCHAR(255),
    dados JSON,
    objetivos TEXT,
    diretoria TEXT,
    disposicoes TEXT,
    pdf VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: slider
-- ============================================================
CREATE TABLE IF NOT EXISTS slider (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texto VARCHAR(255) NOT NULL,
    imagem LONGTEXT,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: config (logo e configurações)
-- ============================================================
CREATE TABLE IF NOT EXISTS config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logo_img LONGTEXT,
    logo_txt VARCHAR(100) DEFAULT 'ASSGA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: index_conteudo (página inicial)
-- ============================================================
CREATE TABLE IF NOT EXISTS index_conteudo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    texto TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERTS INICIAIS (dados de exemplo)
-- ============================================================

-- Configuração padrão
INSERT INTO config (logo_txt) VALUES ('ASSGA');

-- Slider padrão
INSERT INTO slider (texto, imagem, ordem) VALUES
('ASSGA - Associação Desportiva', 'src/imagens/foto1.jpg', 1),
('Esporte e integração da ASSGA', 'src/imagens/foto2.jpg', 2),
('Futsal e atividades esportivas ASSGA', 'src/imagens/foto3.jpg', 3);

-- Conteúdo da página inicial
INSERT INTO index_conteudo (titulo, texto) VALUES
('Bem-vindo à ASSGA', 'A Associação Desportiva de São Gonçalo do Amarante promove esporte, cultura e inclusão social.');

-- Estatuto inicial
INSERT INTO estatuto (titulo, subtitulo, dados, objetivos, diretoria, disposicoes) VALUES
('Estatuto da ASSGA', 'Disposições gerais da associação',
'{"fundacao":"2020-01-01","sede":"São Gonçalo do Amarante - RN"}',
'Promover o esporte como ferramenta de inclusão social\nFomentar a prática de atividades físicas\nIntegrar a comunidade',
'Presidente\nVice-Presidente\nSecretário\nTesoureiro\nDiretor de Esportes\nConselho Fiscal',
'As disposições gerais do estatuto regem a associação.');

-- História inicial
INSERT INTO historia (titulo, subtitulo, texto_extra, fundacao, missao, valores, timeline, numeros) VALUES
('Nossa História', 'Conheça a trajetória da ASSGA',
'A ASSGA foi fundada em 2020 com o objetivo de promover o esporte e a integração social.',
'Fundada em 2020, por um grupo de amigos apaixonados por esportes.',
'Promover o esporte como ferramenta de transformação social.',
'Respeito, Inclusão, Trabalho em equipe, Excelência',
'[{"ano":"2020","descricao":"Fundação da ASSGA"},{"ano":"2021","descricao":"Primeiro campeonato de futsal"},{"ano":"2022","descricao":"Expansão para mais modalidades"}]',
'[{"numero":"50","label":"Sócios"},{"numero":"5","label":"Modalidades"},{"numero":"10","label":"Eventos"},{"numero":"100","label":"Participantes"}]');

-- Esportiva inicial
INSERT INTO esportiva (titulo, subtitulo, data_destaque, texto_extra, modalidades, campeonatos) VALUES
('Nossa Esportiva', 'Conheça as modalidades e campeonatos',
'2026-08-15',
'A ASSGA oferece diversas modalidades esportivas para todas as idades.',
'[{"icone":"fa-futbol","nome":"Futebol Society","descricao":"Futebol society para adultos","tag":"Aberto"},{"icone":"fa-basketball-ball","nome":"Basquete 3x3","descricao":"Basquete 3x3","tag":"Iniciante"},{"icone":"fa-volleyball-ball","nome":"Vôlei de Praia","descricao":"Vôlei de praia","tag":"Aberto"}]',
'[{"titulo":"Copa ASSGA 2026","data":"15/03 a 15/12/2026","participantes":"8 equipes","local":"Ginásio Municipal","status":"andamento"}]');

-- Diretoria (exemplos)
INSERT INTO diretoria (nome, cargo, descricao, email, telefone) VALUES
('Nome do Presidente', 'Presidente', 'Responsável pela gestão geral da associação e representação institucional.', 'presidente@assga.com', '(84) 91234-5678'),
('Nome do Vice-Presidente', 'Vice-Presidente', 'Auxilia o presidente na gestão e coordena as atividades administrativas.', 'vice@assga.com', '(84) 92345-6789'),
('Nome do Secretário', 'Secretário', 'Responsável pela documentação, atas e comunicação oficial da associação.', 'secretario@assga.com', '(84) 93456-7890');

-- Notícias (exemplo)
INSERT INTO noticias (titulo, data, resumo, texto) VALUES
('2º HALLOWEEN ASSGA', '15 de agosto de 2026', 'Estão abertas as inscrições para o 2º HALLOWEEN ASSGA!', 'Prepare-se para um evento especial com muita diversão, esporte, integração e confraternização.');

-- Eventos (exemplo)
INSERT INTO eventos (titulo, descricao, data, local, preco, status) VALUES
('Halloween ASSGA', 'Evento especial com atividades esportivas e confraternização.', '2026-10-31', 'Sede da ASSGA', 0.00, 'aberto');

-- Esportivas (exemplo)
INSERT INTO esportivas (titulo, data, texto) VALUES
('Copa ASSGA 2026 - Futsal', '15/03/2026', 'Inscrições abertas para a Copa ASSGA 2026 de Futsal'),
('Campeonato de Basquete 3x3', '01/05/2026', 'Inscrições abertas para o campeonato de Basquete 3x3');

-- ============================================================
-- FIM DO SCRIPT
-- ============================================================x