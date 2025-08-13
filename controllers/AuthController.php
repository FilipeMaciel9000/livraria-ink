<?php
// controllers/AuthController.php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/UsuarioDAO.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Controlador responsável pela autenticação e gerenciamento de usuários.
 * Gerencia login, logout, registro e verificação de sessões.
 */
class AuthController
{
    private UsuarioDAO $usuarioDAO;

    public function __construct(mysqli $conn)
    {
        $this->usuarioDAO = new UsuarioDAO($conn);

        // Gera token CSRF se não existir
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Retorna o token CSRF atual.
     */
    public function getCsrfToken(): string
    {
        return $_SESSION['csrf_token'];
    }

    /**
     * Define uma mensagem de feedback para o usuário.
     */
    private function setMensagem(string $msg, string $tipo = 'alert-info'): void
    {
        $_SESSION['mensagem'] = $msg;
        $_SESSION['tipo_mensagem'] = $tipo;
    }

    /**
     * Redireciona para uma URL específica.
     */
    private function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /**
     * Valida o token CSRF.
     */
    private function validarCsrf(?string $token): bool
    {
        return !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Verifica se o usuário está logado.
     */
    public function usuarioLogado(): bool
    {
        return isset($_SESSION['usuario_id']) && 
               isset($_SESSION['usuario_email']) && 
               !empty($_SESSION['usuario_id']);
    }

    /**
     * Retorna os dados do usuário logado.
     */
    public function getUsuarioLogado(): ?array
    {
        if (!$this->usuarioLogado()) {
            return null;
        }

        return [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'] ?? '',
            'email' => $_SESSION['usuario_email'],
            'tipo' => $_SESSION['usuario_tipo'] ?? 'funcionario'
        ];
    }

    /**
     * Processa o formulário de login.
     */
    public function processarLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Validação CSRF
        if (!$this->validarCsrf($_POST['csrf_token'] ?? '')) {
            $this->setMensagem("Erro na validação do formulário.", "alert-danger");
            $this->redirect('login.php');
        }

        // Sanitização e validação dos dados
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        // Validações básicas
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setMensagem("Por favor, insira um e-mail válido.", "alert-danger");
            $this->redirect('login.php');
        }

        if (empty($senha)) {
            $this->setMensagem("Por favor, insira sua senha.", "alert-danger");
            $this->redirect('login.php');
        }

        // Busca o usuário no banco
        $usuario = $this->usuarioDAO->buscarPorEmail($email);

        // Verifica se o usuário existe e a senha está correta
        if (!$usuario || !password_verify($senha, $usuario->senha)) {
            $this->setMensagem("E-mail ou senha incorretos.", "alert-danger");
            $this->redirect('login.php');
        }

        // Verifica se o usuário está ativo
        if (!$usuario->ativo) {
            $this->setMensagem("Sua conta está inativa. Contate o administrador.", "alert-danger");
            $this->redirect('login.php');
        }

        // Login bem-sucedido - cria a sessão
        $this->criarSessao($usuario);
        
        // Atualiza último login
        $this->usuarioDAO->atualizarUltimoLogin($usuario->id);

        $this->setMensagem("Login realizado com sucesso! Bem-vindo(a), {$usuario->nome}!", "alert-success");
        $this->redirect('index.php');
    }

    /**
     * Cria a sessão do usuário logado.
     */
    private function criarSessao(Usuario $usuario): void
    {
        // Regenera o ID da sessão para segurança
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario->id;
        $_SESSION['usuario_nome'] = $usuario->nome;
        $_SESSION['usuario_email'] = $usuario->email;
        $_SESSION['usuario_tipo'] = $usuario->tipo;
        $_SESSION['login_time'] = time();
    }

    /**
     * Processa o logout do usuário.
     */
    public function processarLogout(): void
    {
        // Limpa todas as variáveis de sessão
        $_SESSION = array();

        // Destrói o cookie de sessão se existir
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroi a sessão
        session_destroy();

        // Inicia nova sessão para mensagem de feedback
        session_start();
        $this->setMensagem("Logout realizado com sucesso!", "alert-success");
        $this->redirect('login.php');
    }

    /**
     * Processa o registro de novo usuário (opcional).
     */
    public function processarRegistro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Validação CSRF
        if (!$this->validarCsrf($_POST['csrf_token'] ?? '')) {
            $this->setMensagem("Erro na validação do formulário.", "alert-danger");
            $this->redirect('registro.php');
        }

        // Sanitização dos dados
        $nome = trim($_POST['nome'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';
        $confirmaSenha = $_POST['confirma_senha'] ?? '';

        // Validações
        if (empty($nome) || strlen($nome) < 2) {
            $this->setMensagem("Nome deve ter pelo menos 2 caracteres.", "alert-danger");
            $this->redirect('registro.php');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setMensagem("Por favor, insira um e-mail válido.", "alert-danger");
            $this->redirect('registro.php');
        }

        if (strlen($senha) < 6) {
            $this->setMensagem("A senha deve ter pelo menos 6 caracteres.", "alert-danger");
            $this->redirect('registro.php');
        }

        if ($senha !== $confirmaSenha) {
            $this->setMensagem("As senhas não coincidem.", "alert-danger");
            $this->redirect('registro.php');
        }

        // Verifica se o e-mail já existe
        if ($this->usuarioDAO->emailExiste($email)) {
            $this->setMensagem("Este e-mail já está cadastrado.", "alert-danger");
            $this->redirect('registro.php');
        }

        // Cria o usuário
        $usuario = new Usuario(
            null,
            $nome,
            $email,
            password_hash($senha, PASSWORD_DEFAULT),
            'funcionario', // Tipo padrão
            true // Ativo por padrão
        );

        // Salva no banco
        if ($this->usuarioDAO->salvar($usuario)) {
            $this->setMensagem("Conta criada com sucesso! Você já pode fazer login.", "alert-success");
            $this->redirect('login.php');
        } else {
            $this->setMensagem("Erro ao criar a conta. Tente novamente.", "alert-danger");
            $this->redirect('registro.php');
        }
    }

    /**
     * Verifica se a sessão ainda é válida (timeout de sessão).
     */
    public function verificarTimeoutSessao(int $tempoLimite = 7200): bool // 2 horas padrão
    {
        if (!$this->usuarioLogado()) {
            return false;
        }

        $loginTime = $_SESSION['login_time'] ?? 0;
        $tempoAtual = time();

        // Se passou do tempo limite, faz logout
        if (($tempoAtual - $loginTime) > $tempoLimite) {
            $this->processarLogout();
            return false;
        }

        // Atualiza o tempo da sessão
        $_SESSION['login_time'] = $tempoAtual;
        return true;
    }

    /**
     * Retorna mensagens de feedback e as remove da sessão.
     */
    public function pegarMensagem(): array
    {
        $msg = $_SESSION['mensagem'] ?? '';
        $tipo = $_SESSION['tipo_mensagem'] ?? '';
        unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);
        return [$msg, $tipo];
    }

    /**
     * Middleware para verificar se o usuário tem permissão de administrador.
     */
    public function verificarAdmin(): bool
    {
        if (!$this->usuarioLogado()) {
            return false;
        }

        return ($_SESSION['usuario_tipo'] ?? '') === 'admin';
    }

    /**
     * Middleware para proteger rotas que exigem autenticação.
     */
    public function protegerRota(): void
    {
        if (!$this->usuarioLogado() || !$this->verificarTimeoutSessao()) {
            $this->setMensagem("Você precisa estar logado para acessar esta página.", "alert-info");
            $this->redirect('login.php');
        }
    }
}
