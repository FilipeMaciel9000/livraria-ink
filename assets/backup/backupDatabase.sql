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

-- Comum
('Café com Deus Pai 2024: Porções Diárias de Paz', 'Júnior Rostirola', 'Vélos', 29.90, 100, 'Comum'),
('A Biblioteca da Meia-Noite', 'Matt Haig', 'Bertrand Brasil', 39.90, 50, 'Comum'),

-- Raro
('O Senhor dos Anéis - Volume Único (Edição Especial)', 'J.R.R. Tolkien', 'HarperCollins', 199.90, 5, 'Raro'),

-- Coleção
('Harry Potter - Coleção Completa (7 Volumes)', 'J.K. Rowling', 'Rocco', 349.90, 10, 'Coleção'),
('As Crônicas de Nárnia - Box Completo', 'C.S. Lewis', 'WMF Martins Fontes', 199.90, 8, 'Coleção'),

-- Avulso
('Dom Casmurro', 'Machado de Assis', 'Editora Ática', 19.90, 30, 'Avulso'),
('O Cortiço', 'Aluísio Azevedo', 'Editora Saraiva', 22.90, 25, 'Avulso'),

-- Autografado
('Sapiens: Uma Breve História da Humanidade (Autografado)', 'Yuval Noah Harari', 'L&PM', 129.90, 3, 'Autografado'),
('Do Mil ao Milhão (Autografado)', 'Thiago Nigro', 'HarperCollins', 89.90, 4, 'Autografado'),

-- Mais alguns comuns para completar
('Verity', 'Colleen Hoover', 'Galera Record', 49.90, 100, 'Comum'),
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
    'Fidelio', 
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
    'Fidelio',
    'funcionario',
    1,
    NOW(),
    NOW()
);

-- Mostrar os dados da tabela livros
SELECT * FROM livros;
-- Mostrar os dados do usuário administrador
SELECT * FROM usuarios;
