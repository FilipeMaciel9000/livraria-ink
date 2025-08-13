<?php
/**
 * Projeto: Livraria INK — Sistema de Gerenciamento de Estoque de Livros
 * Autor: Filipe Rios Mariz Maciel
 * Data: 26/05/2025
 * Versão: 3.0
 *
 * DESCRIÇÃO:
 * Sistema web desenvolvido em PHP com uso de Programação Orientada a Objetos (POO),
 * voltado para o gerenciamento de acervos de livros. Entre suas principais funcionalidades, destacam-se:
 * - Operações CRUD (Create, Read, Update, Delete)
 * - Busca com filtros por título, autor e status
 * - Edição e exclusão de registros
 * - Validações básicas de entrada no backend
 * - Proteção contra CSRF (Cross-Site Request Forgery) e XSS (Cross-Site Scripting)
 * - Feedback ao usuário com mensagens de sucesso e erro
 * - Arquitetura MVC (Model-View-Controller) para organização do código
 * - Separação entre lógica de negócio e apresentação (MVC simplificado)
 * - Interface amigável com mensagens visuais personalizadas
 *
 * MELHORIAS IMPLEMENTADAS NA VERSÃO 3.0:
 * - Separação entre HTML e PHP para facilitar a manutenção
 * - Filtros de busca mais seguros e eficientes
 * - Feedback visual com mensagens estilizadas via CSS
 * - Validações básicas reforçadas no backend
 * - Sistema simples de logs para depuração
 * - Código padronizado e melhor organizado
 *
 * PONTOS A SEREM APRIMORADOS NA VERSÃO 4.0:
 *  - Melhoria na segurança com autenticação de usuários (login, logout do pessoal autorizado ao sistema etc.)
 * - Controle de versões de registros (Controller com histórico de alterações)
 * - Validação de dados mais robusta (❌ Isso é coisa de fullstack, esse sistema é apenas backend)
 * - Paginação na listagem de livros (❌ Levemente útil, mas não essencial para o CRUD simples que esse projeto se propõe)
 * - Responsividade e adaptação do layout para dispositivos móveis ( ❌ O foco é PHP, não CSS)
 * - Implementação de testes automatizados (❌ Não vale o esforço aqui, o projeto é pequeno e simples)
 * - Mecanismo de backup automático (❌ Isso é mais relevante para sistemas maiores, não é necessário aqui)
 * - Integração com APIs externas (❌ Não é necessário para um sistema de controle de estoque simples)
 * - Suporte a múltiplos idiomas (❌ i18n, Relevante apenas para produtos reais e definitivamente não é o caso aqui)
 * - Validações no lado do cliente com JavaScript (❌ Não existe lado do cliente em um sistema backend simples de controle de estoque)
 */

// Inclusão dos arquivos principais: conexão, modelo e DAO
require __DIR__ .'/utils/conexao.php';
require __DIR__ . '/models/Livro.php';
require __DIR__ . '/models/LivroDAO.php';

session_start();
header('Content-Type: text/html; charset=utf-8');

// Inicializa token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$livroDAO = new LivroDAO($conn);
$livroEdit = null;
$mensagem = $_SESSION['mensagem'] ?? '';
$tipoMensagem = $_SESSION['tipo_mensagem'] ?? '';
unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);

// Lógica de POST (formulário)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'controllers/processa_formulario.php';
    exit;
}

// Exclusão
if (isset($_GET['excluir'])) {
    require 'controllers/excluir_livro.php';
    exit;
}

// Edição
if (isset($_GET['editar'])) {
    $idEdit = intval($_GET['editar']);
    $livroEdit = $livroDAO->buscarPorId($idEdit);
    if (!$livroEdit) {
        $_SESSION['mensagem'] = "Livro não encontrado.";
        $_SESSION['tipo_mensagem'] = "alert-danger";
        header("Location: index.php");
        exit;
    }
}

// Busca
$busca = $_GET['busca'] ?? '';
$statusFiltro = $_GET['status'] ?? '';

try {
    $livros = $livroDAO->listar($busca, $statusFiltro);
} catch (Exception $e) {
    $mensagem = "Erro ao carregar livros.";
    $tipoMensagem = "alert-danger";
    $livros = [];
}

// Renderização da view
require __DIR__ . '/views/home.php';
?>
