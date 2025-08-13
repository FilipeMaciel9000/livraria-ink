<?php
/**
 * Projeto: Livraria INK
 * Descrição: Middleware de autenticação para proteção de rotas e controle de acesso.
 * Este arquivo contém funções utilitárias para verificar autenticação, permissões e 
 * proteger páginas que requerem login. Centraliza a lógica de segurança da aplicação.
 * 
 * Funcionalidades:
 * - Verificação de usuário logado
 * - Proteção de rotas (redirecionamento automático)
 * - Controle de permissões (admin vs funcionario)
 * - Gerenciamento de timeout de sessão
 * - Funções auxiliares de segurança
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Verifica se existe um usuário logado na sessão atual.
 *
 * @return bool True se usuário está logado, False caso contrário
 */
function usuarioLogado(): bool
{
    return isset($_SESSION['usuario_id']) && 
           isset($_SESSION['usuario_email']) && 
           !empty($_SESSION['usuario_id']) &&
           is_numeric($_SESSION['usuario_id']);
}

/**
 * Retorna os dados do usuário logado ou null se não logado.
 *
 * @return array|null Array com dados do usuário ou null
 */
function getUsuarioLogado(): ?array
{
    if (!usuarioLogado()) {
        return null;
    }

    return [
        'id' => (int)$_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'] ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
        'tipo' => $_SESSION['usuario_tipo'] ?? 'funcionario'
    ];
}

/**
 * Verifica se o usuário logado é administrador.
 *
 * @return bool True se é admin, False caso contrário
 */
function isAdmin(): bool
{
    return usuarioLogado() && ($_SESSION['usuario_tipo'] ?? '') === 'admin';
}

/**
 * Verifica se o usuário logado é funcionário.
 *
 * @return bool True se é funcionário, False caso contrário
 */
function isFuncionario(): bool
{
    return usuarioLogado() && ($_SESSION['usuario_tipo'] ?? '') === 'funcionario';
}

/**
 * Protege uma rota exigindo autenticação.
 * Redireciona para login se usuário não estiver logado.
 *
 * @param string $redirectUrl URL para redirecionar se não autenticado
 * @param string $mensagem Mensagem a ser exibida no login
 */
function protegerRota(string $redirectUrl = 'login.php', string $mensagem = ''): void
{
    if (!usuarioLogado() || !verificarTimeoutSessao()) {
        if (empty($mensagem)) {
            $mensagem = "Você precisa estar logado para acessar esta página.";
        }
        
        setMensagemFlash($mensagem, 'alert-info');
        redirecionarPara($redirectUrl);
    }
}

/**
 * Protege uma rota exigindo permissões de administrador.
 * Redireciona se não for admin.
 *
 * @param string $redirectUrl URL para redirecionar se não autorizado
 * @param string $mensagem Mensagem a ser exibida
 */
function protegerRotaAdmin(string $redirectUrl = 'index.php', string $mensagem = ''): void
{
    protegerRota(); // Primeiro verifica se está logado
    
    if (!isAdmin()) {
        if (empty($mensagem)) {
            $mensagem = "Você não tem permissão para acessar esta página.";
        }
        
        setMensagemFlash($mensagem, 'alert-danger');
        redirecionarPara($redirectUrl);
    }
}

/**
 * Verifica timeout da sessão e renova se ainda válida.
 *
 * @param int $tempoLimite Tempo limite em segundos (padrão: 2 horas)
 * @return bool True se sessão válida, False se expirou
 */
function verificarTimeoutSessao(int $tempoLimite = 7200): bool
{
    if (!usuarioLogado()) {
        return false;
    }

    $loginTime = $_SESSION['login_time'] ?? 0;
    $tempoAtual = time();

    // Se passou do tempo limite, destroi a sessão
    if (($tempoAtual - $loginTime) > $tempoLimite) {
        logout();
        return false;
    }

    // Atualiza o tempo da sessão (renovação automática)
    $_SESSION['login_time'] = $tempoAtual;
    return true;
}

/**
 * Realiza logout completo do usuário.
 *
 * @param string $mensagem Mensagem a ser exibida após logout
 * @param string $redirectUrl URL para redirecionar após logout
 */
function logout(string $mensagem = '', string $redirectUrl = 'login.php'): void
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

    // Inicia nova sessão para a mensagem de feedback
    session_start();
    
    if (empty($mensagem)) {
        $mensagem = "Logout realizado com sucesso!";
    }
    
    setMensagemFlash($mensagem, 'alert-success');
    redirecionarPara($redirectUrl);
}

/**
 * Define uma mensagem flash para ser exibida na próxima requisição.
 *
 * @param string $mensagem Texto da mensagem
 * @param string $tipo Tipo da mensagem (alert-success, alert-danger, etc.)
 */
function setMensagemFlash(string $mensagem, string $tipo = 'alert-info'): void
{
    $_SESSION['mensagem'] = $mensagem;
    $_SESSION['tipo_mensagem'] = $tipo;
}

/**
 * Recupera e remove mensagem flash da sessão.
 *
 * @return array [mensagem, tipo]
 */
function getMensagemFlash(): array
{
    $mensagem = $_SESSION['mensagem'] ?? '';
    $tipo = $_SESSION['tipo_mensagem'] ?? '';
    
    // Remove da sessão após recuperar
    unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);
    
    return [$mensagem, $tipo];
}

/**
 * Redireciona para uma URL específica e encerra a execução.
 *
 * @param string $url URL de destino
 */
function redirecionarPara(string $url): void
{
    // Evita redirecionamento duplo
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    }
    
    // Fallback se headers já foram enviados
    echo "<script>window.location.href='$url';</script>";
    exit;
}

/**
 * Gera e retorna token CSRF para proteção de formulários.
 *
 * @return string Token CSRF
 */
function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF enviado no formulário.
 *
 * @param string|null $token Token a ser validado
 * @return bool True se válido, False caso contrário
 */
function validarCsrfToken(?string $token): bool
{
    return !empty($token) && 
           !empty($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenera token CSRF (útil após operações sensíveis).
 */
function regenerarCsrfToken(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Verifica se o usuário atual pode acessar/modificar dados de outro usuário.
 * Admins podem acessar qualquer usuário, funcionários só a si mesmos.
 *
 * @param int $usuarioId ID do usuário alvo
 * @return bool True se pode acessar, False caso contrário
 */
function podeAcessarUsuario(int $usuarioId): bool
{
    if (!usuarioLogado()) {
        return false;
    }
    
    // Admin pode acessar qualquer usuário
    if (isAdmin()) {
        return true;
    }
    
    // Funcionário só pode acessar seus próprios dados
    return (int)$_SESSION['usuario_id'] === $usuarioId;
}

/**
 * Registra atividade do usuário para auditoria.
 *
 * @param string $acao Ação realizada
 * @param array $detalhes Detalhes adicionais (opcional)
 */
function registrarAtividade(string $acao, array $detalhes = []): void
{
    if (!usuarioLogado()) {
        return;
    }
    
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'usuario_id' => $_SESSION['usuario_id'],
        'usuario_nome' => $_SESSION['usuario_nome'] ?? '',
        'acao' => $acao,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'detalhes' => $detalhes
    ];
    
    // Aqui você pode salvar no banco, arquivo de log, etc.
    error_log("ATIVIDADE_USUARIO: " . json_encode($log));
}

/**
 * Verifica se a requisição atual é AJAX.
 *
 * @return bool True se for AJAX, False caso contrário
 */
function isAjaxRequest(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Resposta JSON para requisições AJAX (útil para APIs).
 *
 * @param mixed $data Dados para retornar
 * @param int $httpCode Código HTTP de resposta
 */
function respostaJson($data, int $httpCode = 200): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Middleware principal para incluir no início de páginas protegidas.
 * Combina verificação de login e timeout de sessão.
 */
function middlewareAuth(): void
{
    protegerRota();
}

/**
 * Middleware para páginas que requerem permissões de administrador.
 */
function middlewareAdmin(): void
{
    protegerRotaAdmin();
}

/**
 * Sanitiza dados de entrada para prevenir XSS.
 *
 * @param mixed $data Dados a serem sanitizados
 * @return mixed Dados sanitizados
 */
function sanitizarEntrada($data)
{
    if (is_array($data)) {
        return array_map('sanitizarEntrada', $data);
    }
    
    if (is_string($data)) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Função de conveniência para debug.
 *
 * @param mixed $data Dados para debug
 * @param bool $die Se deve parar execução
 */
function debug($data, bool $die = false): void
{
    // Verifica se estamos em ambiente de desenvolvimento (localhost)
    $isLocalhost = isset($_SERVER['SERVER_NAME']) && 
                   in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']);
    
    if ($isLocalhost) {
        echo '<pre style="background: #f4f4f4; padding: 10px; border: 1px solid #ddd; margin: 10px 0;">';
        print_r($data);
        echo '</pre>';
        
        if ($die) {
            die();
        }
    }
}