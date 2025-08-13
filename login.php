<?php
// login.php - Ponto de entrada para autenticação

require __DIR__ . '/utils/conexao.php';
require __DIR__ . '/models/Usuario.php';
require __DIR__ . '/models/UsuarioDAO.php';
require __DIR__ . '/controllers/AuthController.php';

header('Content-Type: text/html; charset=utf-8');

$authController = new AuthController($conn);

// Verifica se usuário já está logado
if ($authController->usuarioLogado()) {
    header('Location: index.php');
    exit;
}

// Processa formulário de login (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->processarLogin();
}

// Pega mensagens para exibir na view
[$mensagem, $tipoMensagem] = $authController->pegarMensagem();

// Passa para a view de login
require __DIR__ . '/views/login.php';
