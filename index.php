<?php
// index.php - Sistema de Controle de Estoque da Livraria INK
// Versão com sistema de autenticação integrado

// Carrega o middleware de autenticação
require_once 'utils/middleware.php';

// Protege a rota - redireciona para login se não autenticado
middlewareAuth();

// Carrega as dependências do sistema
require __DIR__ . '/utils/conexao.php';
require __DIR__ . '/models/Livro.php';
require __DIR__ . '/models/LivroDAO.php';
require __DIR__ . '/controllers/LivroController.php';

header('Content-Type: text/html; charset=utf-8');

// Processa logout se solicitado
if (isset($_GET['logout'])) {
    registrarAtividade('Logout realizado');
    logout("Logout realizado com sucesso!", "login.php");
}

$controller = new LivroController($conn);

// Registra atividade de acesso ao sistema
registrarAtividade('Acesso ao sistema de estoque');

// Processa ações primeiro (POST ou GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registra atividade dependendo da ação
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        registrarAtividade('Atualização de livro', ['livro_id' => $_POST['id']]);
    } else {
        registrarAtividade('Cadastro de novo livro', ['titulo' => $_POST['titulo'] ?? '']);
    }
    
    $controller->processarFormulario();
}

if (isset($_GET['excluir'])) {
    registrarAtividade('Exclusão de livro', ['livro_id' => $_GET['excluir']]);
    $controller->processarExclusao();
}

// Busca livro para edição (se houver)
$livroEdit = $controller->buscarLivroParaEdicao();

// Lista livros para exibir
$livros = $controller->listarLivros();

// Pega mensagens para exibir (primeiro do middleware, depois do controller)
[$mensagemAuth, $tipoMensagemAuth] = getMensagemFlash();
[$mensagemController, $tipoMensagemController] = $controller->pegarMensagem();

// Prioriza mensagem do controller se existir, senão usa do middleware
$mensagem = $mensagemController ?: $mensagemAuth;
$tipoMensagem = $mensagemController ? $tipoMensagemController : $tipoMensagemAuth;

// Obtém os filtros de busca para a view
$filtros = $controller->getFiltros();
$busca = $filtros['busca'];
$statusFiltro = $filtros['statusFiltro'];

// Dados do usuário logado para a view
$usuarioLogado = getUsuarioLogado();

// Passa variáveis para a view:
// - $livroEdit: livro sendo editado (se houver)
// - $livros: lista de livros
// - $mensagem, $tipoMensagem: mensagens de feedback
// - $busca, $statusFiltro: filtros de busca
// - $usuarioLogado: dados do usuário autenticado
// - $controller->getCsrfToken(): token CSRF
require __DIR__ . '/views/home.php';
