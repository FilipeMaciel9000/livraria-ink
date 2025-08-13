<?php

/**
 * Projeto: Livraria INK
 * Descrição: Classe de acesso a dados (DAO) responsável por gerenciar operações relacionadas a livros no banco de dados.
 * Encapsula a lógica de persistência e recuperação dos objetos Livro, promovendo a separação de responsabilidades entre as camadas de negócio e de armazenamento de dados.
 * 
 * Funcionalidades:
 * Inserção e atualização de registros de livros.
 * Exclusão de livros por ID.
 * Consulta individual por ID ou listagem com filtros de busca e status.
 */


require_once 'Livro.php';

/**
 * Classe responsável pela manipulação dos dados dos livros no banco.
 */
class LivroDAO
{
    private mysqli $conn;
    private const STATUS_OPTIONS = ['Comum', 'Raro', 'Coleção', 'Avulso', 'Autografado'];

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Insere ou atualiza um livro no banco de dados.
     */
    public function salvar(Livro $livro): bool
    {
        try {
            if ($livro->id) {
                $stmt = $this->conn->prepare(
                    "UPDATE livros SET titulo = ?, autor = ?, editora = ?, quantidade = ?, preco = ?, status = ? WHERE id = ?"
                );
                $stmt->bind_param(
                    "sssidsi",
                    $livro->titulo,
                    $livro->autor,
                    $livro->editora,
                    $livro->quantidade,
                    $livro->preco,
                    $livro->status,
                    $livro->id
                );
            } else {
                $stmt = $this->conn->prepare(
                    "INSERT INTO livros (titulo, autor, editora, quantidade, preco, status) VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "sssids",
                    $livro->titulo,
                    $livro->autor,
                    $livro->editora,
                    $livro->quantidade,
                    $livro->preco,
                    $livro->status
                );
            }

            $resultado = $stmt->execute();
            $stmt->close();
            return $resultado;
        } catch (mysqli_sql_exception $e) {
            error_log("Erro ao salvar livro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um livro pelo ID.
     */
    public function excluir(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM livros WHERE id = ?");
        $stmt->bind_param("i", $id);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }


    /**
     * Busca um livro pelo ID.
     */
    public function buscarPorId(int $id): ?Livro
    {
        $stmt = $this->conn->prepare("SELECT * FROM livros WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $livro = null;

        if ($res && $row = $res->fetch_assoc()) {
            $livro = Livro::fromArray($row);
        }

        $stmt->close();
        return $livro;
    }

    /**
     * Lista todos os livros, com filtros opcionais de busca e status.
     *
     * @param string $busca  Título ou autor parcial
     * @param string $status Status do livro
     * @return Livro[]
     */
    public function listar(string $busca = '', string $status = ''): array
    {
        $where = [];
        $params = [];
        $types = '';

        if ($busca !== '') {
            $where[] = "(titulo LIKE ? OR autor LIKE ?)";
            $buscaLike = "%$busca%";
            $params[] = $buscaLike;
            $params[] = $buscaLike;
            $types .= 'ss';
        }

        if ($status !== '' && in_array($status, self::STATUS_OPTIONS)) {
            $where[] = "status = ?";
            $params[] = $status;
            $types .= 's';
        }

        $sql = "SELECT * FROM livros";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result();

        $livros = [];
        while ($row = $res->fetch_assoc()) {
            $livros[] = Livro::fromArray($row);
        }

        $stmt->close();
        return $livros;
    }
    
}

