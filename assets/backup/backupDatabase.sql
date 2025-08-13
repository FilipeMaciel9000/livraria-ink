-- Isso, obviamente, náo é o banco de dados de verdade
-- O verdadeiro está no phpMyAdmin
-- Isso é o backup do script de criação do banco de dados
-- Se algo der errado, dá para restaurar o banco de dados inteiro do zero com esse script
-- Guarde com carinho!

-- Criar o banco de dados (caso ainda não exista)
CREATE DATABASE IF NOT EXISTS livraria
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

-- Usar o banco de dados
USE livraria;

-- Criar a tabela livros
CREATE TABLE livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    autor VARCHAR(255) NOT NULL,
    editora VARCHAR(255),
    quantidade INT DEFAULT 0,
    preco DECIMAL(10, 2) NOT NULL,
    status ENUM('Comum', 'Raro', 'Coleção', 'Avulso', 'Autografado') NOT NULL DEFAULT 'Comum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserindo dados na tabela livros
INSERT INTO livros (titulo, autor, editora, preco, quantidade, status) VALUES
('Café com Deus Pai 2024: Porções Diárias de Paz', 'Júnior Rostirola', 'Vélos', 29.90, 100, 'Comum'),
('A Biblioteca da Meia-Noite', 'Matt Haig', 'Bertrand Brasil', 39.90, 100, 'Comum'),
('É Assim que Acaba', 'Colleen Hoover', 'Galera Record', 49.90, 100, 'Comum'),
('É Assim que Começa', 'Colleen Hoover', 'Galera Record', 49.90, 100, 'Comum'),
('O Homem Mais Rico da Babilônia', 'George S. Clason', 'HarperCollins', 29.90, 100, 'Comum'),
('Tudo é Rio', 'Carla Madeira', 'Record', 39.90, 100, 'Comum'),
('A Psicologia Financeira', 'Morgan Housel', 'HarperCollins', 49.90, 100, 'Comum'),
('Verity', 'Colleen Hoover', 'Galera Record', 49.90, 100, 'Comum'),
('Perigoso!', 'Tim Warnes', 'Cia. das Letrinhas', 19.90, 100, 'Comum'),
('Como Fazer Amigos e Influenciar Pessoas', 'Dale Carnegie', 'Sextante', 29.90, 100, 'Comum');


-- Criação da tabela de dados dos usuários
-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'funcionario') DEFAULT 'funcionario',
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criar usuário administrador
INSERT INTO usuarios (nome, email, senha, tipo, ativo, created_at, updated_at) 
VALUES (
    'Administrador',
    'admin@livrariank.com',
    '$2y$10$j4itlUavNBOOt1c3XnXYouuVo2s8nqolTuMeCd4IiNftuyNzahHBO', 
    'admin',
    1,
    NOW(),
    NOW()
);

-- Criar usuário funcionário
INSERT INTO usuarios (nome, email, senha, tipo, ativo, created_at, updated_at) 
VALUES (
    'João Silva',
    'joao@livrariank.com',
    '$2y$10$j4itlUavNBOOt1c3XnXYouuVo2s8nqolTuMeCd4IiNftuyNzahHBOi',
    'funcionario',
    1,
    NOW(),
    NOW()
);

-- Mostrar os dados da tabela livros
SELECT * FROM livros;
-- Mostrar os dados do usuário administrador
SELECT * FROM usuarios;
