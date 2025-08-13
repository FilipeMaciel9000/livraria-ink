<?php

/**
 * Projeto: Livraria INK
 * Descrição: Classe de acesso a dados (DAO) responsável por gerenciar operações relacionadas a usuários no banco de dados.
 * Encapsula a lógica de persistência e recuperação dos objetos Usuario, promovendo a separação de responsabilidades
 * entre as camadas de negócio e de armazenamento de dados.
 * 
 * Funcionalidades:
 * - Inserção e atualização de registros de usuários.
 * - Exclusão e ativação/desativação de usuários.
 * - Consulta individual por ID ou email.
 * - Listagem com filtros de busca e tipo.
 * - Autenticação e controle de login.
 */

require_once 'Usuario.php';

/**
 * Classe responsável pela manipulação dos dados dos usuários no banco.
 */
class UsuarioDAO
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Insere ou atualiza um usuário no banco de dados.
     *
     * @param Usuario $usuario
     * @return bool
     */
    public function salvar(Usuario $usuario): bool
    {
        try {
            if ($usuario->id) {
                // Atualização
                $stmt = $this->conn->prepare(
                    "UPDATE usuarios SET nome = ?, email = ?, senha = ?, tipo = ?, ativo = ?, updated_at = NOW() WHERE id = ?"
                );
                $stmt->bind_param(
                    "sssisi",
                    $usuario->nome,
                    $usuario->email,
                    $usuario->senha,
                    $usuario->tipo,
                    $usuario->ativo,
                    $usuario->id
                );
            } else {
                // Inserção
                $stmt = $this->conn->prepare(
                    "INSERT INTO usuarios (nome, email, senha, tipo, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
                );
                $stmt->bind_param(
                    "ssssi",
                    $usuario->nome,
                    $usuario->email,
                    $usuario->senha,
                    $usuario->tipo,
                    $usuario->ativo
                );
            }

            $resultado = $stmt->execute();
            
            // Se foi inserção bem-sucedida, pega o ID gerado
            if ($resultado && !$usuario->id) {
                $usuario->id = $this->conn->insert_id;
            }
            
            $stmt->close();
            return $resultado;
            
        } catch (mysqli_sql_exception $e) {
            error_log("Erro ao salvar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca um usuário pelo ID.
     *
     * @param int $id
     * @return Usuario|null
     */
    public function buscarPorId(int $id): ?Usuario
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $usuario = null;

        if ($res && $row = $res->fetch_assoc()) {
            $usuario = Usuario::fromArray($row);
        }

        $stmt->close();
        return $usuario;
    }

    /**
     * Busca um usuário pelo email.
     *
     * @param string $email
     * @return Usuario|null
     */
    public function buscarPorEmail(string $email): ?Usuario
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $usuario = null;

        if ($res && $row = $res->fetch_assoc()) {
            $usuario = Usuario::fromArray($row);
        }

        $stmt->close();
        return $usuario;
    }

    /**
     * Verifica se um email já existe no banco.
     *
     * @param string $email
     * @param int|null $excluirId ID para excluir da verificação (útil em atualizações)
     * @return bool
     */
    public function emailExiste(string $email, ?int $excluirId = null): bool
    {
        if ($excluirId) {
            $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $excluirId);
        } else {
            $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
        }
        
        $stmt->execute();
        $res = $stmt->get_result();
        $existe = $res->num_rows > 0;
        $stmt->close();
        
        return $existe;
    }

    /**
     * Lista todos os usuários com filtros opcionais.
     *
     * @param string $busca Busca por nome ou email
     * @param string $tipo Filtro por tipo de usuário
     * @param bool|null $ativo Filtro por status ativo (null = todos)
     * @return Usuario[]
     */
    public function listar(string $busca = '', string $tipo = '', ?bool $ativo = null): array
    {
        $where = [];
        $params = [];
        $types = '';

        if ($busca !== '') {
            $where[] = "(nome LIKE ? OR email LIKE ?)";
            $buscaLike = "%$busca%";
            $params[] = $buscaLike;
            $params[] = $buscaLike;
            $types .= 'ss';
        }

        if ($tipo !== '' && in_array($tipo, Usuario::TIPOS_VALIDOS)) {
            $where[] = "tipo = ?";
            $params[] = $tipo;
            $types .= 's';
        }

        if ($ativo !== null) {
            $where[] = "ativo = ?";
            $params[] = $ativo;
            $types .= 'i';
        }

        $sql = "SELECT * FROM usuarios";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result();

        $usuarios = [];
        while ($row = $res->fetch_assoc()) {
            $usuarios[] = Usuario::fromArray($row);
        }

        $stmt->close();
        return $usuarios;
    }

    /**
     * Ativa ou desativa um usuário (soft delete).
     *
     * @param int $id
     * @param bool $ativo
     * @return bool
     */
    public function alterarStatus(int $id, bool $ativo): bool
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET ativo = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ii", $ativo, $id);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Exclui um usuário permanentemente do banco (use com cuidado).
     *
     * @param int $id
     * @return bool
     */
    public function excluir(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Atualiza o último login do usuário.
     *
     * @param int $id
     * @return bool
     */
    public function atualizarUltimoLogin(int $id): bool
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Altera a senha de um usuário.
     *
     * @param int $id
     * @param string $novaSenhaHash
     * @return bool
     */
    public function alterarSenha(int $id, string $novaSenhaHash): bool
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET senha = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $novaSenhaHash, $id);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Conta o total de usuários com filtros opcionais.
     *
     * @param string $tipo
     * @param bool|null $ativo
     * @return int
     */
    public function contar(string $tipo = '', ?bool $ativo = null): int
    {
        $where = [];
        $params = [];
        $types = '';

        if ($tipo !== '' && in_array($tipo, Usuario::TIPOS_VALIDOS)) {
            $where[] = "tipo = ?";
            $params[] = $tipo;
            $types .= 's';
        }

        if ($ativo !== null) {
            $where[] = "ativo = ?";
            $params[] = $ativo;
            $types .= 'i';
        }

        $sql = "SELECT COUNT(*) as total FROM usuarios";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        return (int)($row['total'] ?? 0);
    }

    /**
     * Busca usuários que não fizeram login há X dias.
     *
     * @param int $dias
     * @return Usuario[]
     */
    public function buscarInativos(int $dias = 30): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM usuarios 
             WHERE ativo = 1 
             AND (ultimo_login IS NULL OR ultimo_login < DATE_SUB(NOW(), INTERVAL ? DAY))
             ORDER BY ultimo_login ASC"
        );
        $stmt->bind_param("i", $dias);
        $stmt->execute();
        $res = $stmt->get_result();

        $usuarios = [];
        while ($row = $res->fetch_assoc()) {
            $usuarios[] = Usuario::fromArray($row);
        }

        $stmt->close();
        return $usuarios;
    }

    /**
     * Cria o primeiro usuário administrador (para instalação inicial).
     *
     * @param string $nome
     * @param string $email
     * @param string $senha
     * @return bool
     */
    public function criarAdmin(string $nome, string $email, string $senha): bool
    {
        // Verifica se já existe algum admin
        if ($this->contar(Usuario::TIPO_ADMIN) > 0) {
            return false; // Já existe admin
        }

        $admin = new Usuario(
            null,
            Usuario::sanitizarNome($nome),
            Usuario::sanitizarEmail($email),
            Usuario::hashSenha($senha),
            Usuario::TIPO_ADMIN,
            true
        );

        return $this->salvar($admin);
    }
}