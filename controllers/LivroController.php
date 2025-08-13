<?php
// controllers/LivroController.php

require_once __DIR__ . '/../models/Livro.php';
require_once __DIR__ . '/../models/LivroDAO.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

class LivroController
{
    private LivroDAO $livroDAO;

    public function __construct(mysqli $conn)
    {
        $this->livroDAO = new LivroDAO($conn);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function getFiltros(): array
{
    return [
        'busca' => $_GET['busca'] ?? '',
        'statusFiltro' => $_GET['status'] ?? '',
    ];
}


    public function getCsrfToken(): string
    {
        return $_SESSION['csrf_token'];
    }

    private function setMensagem(string $msg, string $tipo = 'alert-info'): void
    {
        $_SESSION['mensagem'] = $msg;
        $_SESSION['tipo_mensagem'] = $tipo;
    }

    private function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    private function validarCsrf(?string $token): bool
    {
        return !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function processarFormulario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!$this->validarCsrf($_POST['csrf_token'] ?? '')) {
            $this->setMensagem("Erro na validação do formulário (CSRF).", "alert-danger");
            $this->redirect('index.php');
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : null;
        $titulo = trim($_POST['titulo'] ?? '');
        $autor = trim($_POST['autor'] ?? '');
        $editora = trim($_POST['editora'] ?? '');

        $quantidadeRaw = $_POST['quantidade'] ?? '';
        $quantidade = filter_var($quantidadeRaw, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

        $precoRaw = str_replace(',', '.', $_POST['preco'] ?? '');
        $preco = filter_var($precoRaw, FILTER_VALIDATE_FLOAT);

        $status = $_POST['status'] ?? 'Comum';

        if (
            empty($titulo) || empty($autor) || empty($editora) ||
            $quantidade === false || $quantidade < 1 ||
            $preco === false || $preco <= 0 ||
            !in_array($status, Livro::STATUS_VALIDOS)
        ) {
            $this->setMensagem("Por favor, preencha todos os campos corretamente.", "alert-danger");
            $this->redirect('index.php');
        }

        $livro = new Livro($id, $titulo, $autor, $editora, $quantidade, $preco, $status);

        if ($this->livroDAO->salvar($livro)) {
            $this->setMensagem($id ? "Livro atualizado com sucesso!" : "Livro cadastrado com sucesso!", "alert-success");
        } else {
            $this->setMensagem("Erro ao salvar o livro.", "alert-danger");
        }
        $this->redirect('index.php');
    }

    public function processarExclusao(): void
    {
        if (!isset($_GET['excluir'])) {
            return;
        }

        $idExcluir = intval($_GET['excluir']);
        if ($idExcluir <= 0) {
            $this->setMensagem("ID inválido para exclusão.", "alert-warning");
            $this->redirect('index.php');
        }

        $livro = $this->livroDAO->buscarPorId($idExcluir);
        if (!$livro) {
            $this->setMensagem("Livro não encontrado.", "alert-danger");
            $this->redirect('index.php');
        }

        if ($this->livroDAO->excluir($idExcluir)) {
            $this->setMensagem("Livro excluído com sucesso!", "alert-success");
        } else {
            $this->setMensagem("Erro ao tentar excluir o livro.", "alert-danger");
        }
        $this->redirect('index.php');
    }

    public function buscarLivroParaEdicao(): ?Livro
    {
        if (!isset($_GET['editar'])) {
            return null;
        }

        $idEditar = intval($_GET['editar']);
        if ($idEditar <= 0) {
            $this->setMensagem("ID inválido para edição.", "alert-warning");
            $this->redirect('index.php');
        }

        $livro = $this->livroDAO->buscarPorId($idEditar);
        if (!$livro) {
            $this->setMensagem("Livro não encontrado.", "alert-danger");
            $this->redirect('index.php');
        }
        return $livro;
    }

    public function listarLivros(): array
    {
        $busca = $_GET['busca'] ?? '';
        $statusFiltro = $_GET['status'] ?? '';

        try {
            return $this->livroDAO->listar($busca, $statusFiltro);
        } catch (Exception $e) {
            $this->setMensagem("Erro ao carregar livros.", "alert-danger");
            return [];
        }
    }

    public function pegarMensagem(): array
    {
        $msg = $_SESSION['mensagem'] ?? '';
        $tipo = $_SESSION['tipo_mensagem'] ?? '';
        unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);
        return [$msg, $tipo];
    }
}
