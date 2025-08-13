<?php

/**
 * Projeto: Livraria INK
 * Descrição: Esta classe representa um usuário no sistema de gerenciamento da Livraria INK.
 * Ela encapsula propriedades essenciais como nome, email, senha, tipo de usuário e status ativo.
 * Inclui também mecanismos de validação para garantir integridade e segurança dos dados.
 */

class Usuario
{
    public const TIPOS_VALIDOS = ['admin', 'funcionario'];
    public const TIPO_ADMIN = 'admin';
    public const TIPO_FUNCIONARIO = 'funcionario';

    public ?int $id;
    public string $nome;
    public string $email;
    public string $senha;
    public string $tipo;
    public bool $ativo;
    public ?string $ultimoLogin;
    public ?string $criadoEm;
    public ?string $atualizadoEm;

    /**
     * Construtor da classe Usuario.
     *
     * @param int|null $id
     * @param string $nome
     * @param string $email
     * @param string $senha
     * @param string $tipo
     * @param bool $ativo
     * @param string|null $ultimoLogin
     * @param string|null $criadoEm
     * @param string|null $atualizadoEm
     */
    public function __construct(
        ?int $id = null,
        string $nome = '',
        string $email = '',
        string $senha = '',
        string $tipo = self::TIPO_FUNCIONARIO,
        bool $ativo = true,
        ?string $ultimoLogin = null,
        ?string $criadoEm = null,
        ?string $atualizadoEm = null
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->setTipo($tipo);
        $this->ativo = $ativo;
        $this->ultimoLogin = $ultimoLogin;
        $this->criadoEm = $criadoEm;
        $this->atualizadoEm = $atualizadoEm;
    }

    /**
     * Define o tipo do usuário, com validação.
     *
     * @param string $tipo
     */
    public function setTipo(string $tipo): void
    {
        $this->tipo = in_array($tipo, self::TIPOS_VALIDOS) ? $tipo : self::TIPO_FUNCIONARIO;
    }

    /**
     * Verifica se o usuário é administrador.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->tipo === self::TIPO_ADMIN;
    }

    /**
     * Verifica se o usuário é funcionário.
     *
     * @return bool
     */
    public function isFuncionario(): bool
    {
        return $this->tipo === self::TIPO_FUNCIONARIO;
    }

    /**
     * Verifica se o usuário está ativo.
     *
     * @return bool
     */
    public function isAtivo(): bool
    {
        return $this->ativo;
    }

    /**
     * Define o status ativo do usuário.
     *
     * @param bool $ativo
     */
    public function setAtivo(bool $ativo): void
    {
        $this->ativo = $ativo;
    }

    /**
     * Valida se o email tem formato válido.
     *
     * @return bool
     */
    public function emailValido(): bool
    {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida se o nome tem tamanho adequado.
     *
     * @return bool
     */
    public function nomeValido(): bool
    {
        return !empty(trim($this->nome)) && strlen(trim($this->nome)) >= 2;
    }

    /**
     * Valida se a senha atende aos critérios mínimos.
     *
     * @return bool
     */
    public function senhaValida(): bool
    {
        return !empty($this->senha) && strlen($this->senha) >= 6;
    }

    /**
     * Valida todos os campos obrigatórios do usuário.
     *
     * @return array Array com erros encontrados (vazio se válido)
     */
    public function validar(): array
    {
        $erros = [];

        if (!$this->nomeValido()) {
            $erros[] = 'Nome deve ter pelo menos 2 caracteres.';
        }

        if (!$this->emailValido()) {
            $erros[] = 'Email deve ter um formato válido.';
        }

        if (!$this->senhaValida()) {
            $erros[] = 'Senha deve ter pelo menos 6 caracteres.';
        }

        if (!in_array($this->tipo, self::TIPOS_VALIDOS)) {
            $erros[] = 'Tipo de usuário inválido.';
        }

        return $erros;
    }

    /**
     * Cria uma instância de Usuario a partir de um array (ex: resultado do banco).
     *
     * @param array $data
     * @return Usuario
     */
    public static function fromArray(array $data): Usuario
    {
        return new Usuario(
            $data['id'] ?? null,
            $data['nome'] ?? '',
            $data['email'] ?? '',
            $data['senha'] ?? '',
            $data['tipo'] ?? self::TIPO_FUNCIONARIO,
            isset($data['ativo']) ? (bool)$data['ativo'] : true,
            $data['ultimo_login'] ?? null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converte o usuário para array (útil para logs ou debug).
     * NOTA: Não inclui a senha por segurança.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'tipo' => $this->tipo,
            'ativo' => $this->ativo,
            'ultimo_login' => $this->ultimoLogin,
            'created_at' => $this->criadoEm,
            'updated_at' => $this->atualizadoEm
        ];
    }

    /**
     * Gera hash da senha usando password_hash().
     *
     * @param string $senha
     * @return string
     */
    public static function hashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    /**
     * Verifica se a senha informada confere com o hash.
     *
     * @param string $senha
     * @param string $hash
     * @return bool
     */
    public static function verificarSenha(string $senha, string $hash): bool
    {
        return password_verify($senha, $hash);
    }

    /**
     * Sanitiza o nome removendo caracteres desnecessários.
     *
     * @param string $nome
     * @return string
     */
    public static function sanitizarNome(string $nome): string
    {
        // Remove tags HTML e espaços extras
        $nome = strip_tags(trim($nome));
        // Remove múltiplos espaços
        $nome = preg_replace('/\s+/', ' ', $nome);
        return $nome;
    }

    /**
     * Sanitiza o email.
     *
     * @param string $email
     * @return string
     */
    public static function sanitizarEmail(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
}