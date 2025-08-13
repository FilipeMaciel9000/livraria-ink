<?php
/**
 * Projeto: Livraria INK — Sistema de gerenciamento de estoque de livros
 * Autor: Filipe Rios Mariz Maciel
 * Data: 13/04/2025
 * Versão: 2.0 — Refatoração com foco em POO, segurança e organização do código
 *
 * DESCRIÇÃO:
 * Aplicação web em PHP orientada a objetos para controle de acervo literário.
 * Permite operações de cadastro, listagem, edição e exclusão de livros (CRUD).
 * Adota boas práticas de segurança, como proteção contra CSRF e uso de prepared statements.
 *
 * CARACTERÍSTICAS IMPLEMENTADAS:
 * - Programação orientada a objetos com classes separadas para modelo e acesso a dados (Livro, LivroDAO).
 * - Proteção contra CSRF em formulários via token armazenado em sessão.
 * - Uso de prepared statements para prevenir SQL Injection.
 * - Validação básica de campos de entrada no backend.
 * - Escapamento de dados de saída com `htmlspecialchars` para mitigar XSS.
 * - Sistema de mensagens de feedback via sessão (sucesso/erro).
 * - Controle de erros com blocos try/catch e logs via `error_log()`.
 * - Exclusão com confirmação explícita via parâmetro GET.
 *
 * LIMITAÇÕES E PONTOS A MELHORAR:
 * - Ausência de funcionalidade de busca e filtros para facilitar a navegação por muitos registros.
 * - Validações de entrada ainda básicas — não há tratamento para strings excessivamente longas ou caracteres especiais.
 * - Mensagens de erro genéricas em exceções — dificultam a depuração.
 * - Possível exposição a XSS em atributos HTML (valores inline) ainda não tratados.
 * - Falta de logs detalhados para ações sensíveis (ex: exclusões).
 * - Ausência de testes automatizados (unitários ou de integração).
 * - Sem controle de versão dos dados (ex: histórico de edições).
 * - Não há política automatizada de backup ou exportação de dados.
 * - Atualizações são realizadas sem confirmação prévia.
 * - Não há interface dedicada de administração ou autenticação de usuários.
 *
 * CONCLUSÃO:
 * Esta versão apresenta arquitetura mais limpa, com avanço em segurança e estrutura.
 * Serve como base sólida para evoluções futuras, especialmente em usabilidade, validação e robustez em ambiente de produção.
 */

session_start();

// Configurações iniciais
header('Content-Type: text/html; charset=utf-8');
require_once 'conexao.php';
require_once 'Livro.php';
require_once 'LivroDAO.php';

// Inicializa CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inicializa DAO
$livroDAO = new LivroDAO($conn);

// Variáveis para formulário
$livroEdit = null;
$mensagem = $_SESSION['mensagem'] ?? '';
$tipoMensagem = $_SESSION['tipo_mensagem'] ?? '';
unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);

// Processa formulário POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificação CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new RuntimeException("Token de segurança inválido!");
        }

        $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
        $livro = new Livro(
            $id,
            trim($_POST['titulo'] ?? ''),
            trim($_POST['autor'] ?? ''),
            trim($_POST['editora'] ?? ''),
            intval($_POST['quantidade'] ?? 0),
            floatval(str_replace(',', '.', $_POST['preco'] ?? 0)),
            $_POST['status'] ?? 'Comum'
        );

        // Validação básica
        if (empty($livro->titulo) || empty($livro->autor) || empty($livro->editora) || 
            $livro->quantidade <= 0 || $livro->preco < 0) {
            throw new InvalidArgumentException("Preencha todos os campos corretamente.");
        }

        $sucesso = $livroDAO->salvar($livro);

        if (!$sucesso) {
            throw new RuntimeException("Erro ao salvar o livro no banco de dados.");
        }

        $_SESSION['mensagem'] = $id ? "Livro atualizado com sucesso!" : "Livro adicionado com sucesso!";
        $_SESSION['tipo_mensagem'] = "alert-success";

    } catch (InvalidArgumentException $e) {
        $_SESSION['mensagem'] = $e->getMessage();
        $_SESSION['tipo_mensagem'] = "alert-danger";
    } catch (Exception $e) {
        $_SESSION['mensagem'] = "Ocorreu um erro no sistema. Por favor, tente novamente.";
        $_SESSION['tipo_mensagem'] = "alert-danger";
        error_log("Erro no sistema: " . $e->getMessage());
    }

    header("Location: index.php");
    exit;
}

// Exclusão
if (isset($_GET['excluir'])) {
    try {
        $idExcluir = intval($_GET['excluir']);
        if ($idExcluir > 0) {
            $sucesso = $livroDAO->excluir($idExcluir);
            
            if (!$sucesso) {
                throw new RuntimeException("Erro ao excluir o livro.");
            }
            
            $_SESSION['mensagem'] = "Livro excluído com sucesso!";
            $_SESSION['tipo_mensagem'] = "alert-success";
        }
    } catch (Exception $e) {
        $_SESSION['mensagem'] = "Ocorreu um erro ao excluir o livro.";
        $_SESSION['tipo_mensagem'] = "alert-danger";
        error_log("Erro ao excluir: " . $e->getMessage());
    }
    
    header("Location: index.php");
    exit;
}

// Edição - carregar dados do livro
if (isset($_GET['editar'])) {
    $idEdit = intval($_GET['editar']);
    if ($idEdit > 0) {
        $livroEdit = $livroDAO->buscarPorId($idEdit);
        if ($livroEdit === null) {
            $_SESSION['mensagem'] = "Livro não encontrado.";
            $_SESSION['tipo_mensagem'] = "alert-danger";
            header("Location: index.php");
            exit;
        }
    }
}

// Obtém lista de livros
try {
    $livros = $livroDAO->listar();
} catch (Exception $e) {
    $mensagem = "Erro ao carregar a lista de livros.";
    $tipoMensagem = "alert-danger";
    error_log("Erro ao listar livros: " . $e->getMessage());
    $livros = [];
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Livraria INK</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="icon" href="css/ink.png" type="image/x-icon" />
</head>
<body>
<header>
    <img src="css/ink.png" alt="Logo Livraria INK" />
    <h1>Livraria INK</h1>
    <h2><em>Gerenciamento de estoque</em></h2>
</header>

<?php if ($mensagem): ?>
    <div class="alert <?= htmlspecialchars($tipoMensagem, ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<form method="POST" action="" class="formulario-livro">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    
    <?php if ($livroEdit && $livroEdit->id !== null): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($livroEdit->id, ENT_QUOTES, 'UTF-8') ?>" />
    <?php endif; ?>

    <div class="campo-form">
        <label for="titulo">Título do livro:</label>
        <input type="text" id="titulo" name="titulo" placeholder="Insira o título do livro"
               value="<?= htmlspecialchars($livroEdit->titulo ?? '', ENT_QUOTES, 'UTF-8') ?>" required />
    </div>

    <div class="campo-form">
        <label for="autor">Autor:</label>
        <input type="text" id="autor" name="autor" placeholder="Insira o nome do autor"
               value="<?= htmlspecialchars($livroEdit->autor ?? '', ENT_QUOTES, 'UTF-8') ?>" required />
    </div>

    <div class="campo-form">
        <label for="editora">Editora:</label>
        <input type="text" id="editora" name="editora" placeholder="Insira o nome da editora"
               value="<?= htmlspecialchars($livroEdit->editora ?? '', ENT_QUOTES, 'UTF-8') ?>" required />
    </div>

    <div class="campo-form">
        <label for="quantidade">Quantidade:</label>
        <input type="number" id="quantidade" name="quantidade" placeholder="Insira a quantidade de livros"
               value="<?= htmlspecialchars($livroEdit->quantidade ?? '', ENT_QUOTES, 'UTF-8') ?>" min="1" required />
    </div>

    <div class="campo-form">
        <label for="preco">Preço (R$):</label>
        <input type="text" id="preco" name="preco" placeholder="Insira o preço individual do livro"
               value="<?= isset($livroEdit->preco) ? number_format($livroEdit->preco, 2, ',', '') : '' ?>"
               pattern="^\d+(\,\d{1,2})?$" title="Formato: 0,00" required />
    </div>

    <div class="campo-form">
        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <?php foreach (Livro::STATUS_VALIDOS as $option): ?>
                <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"
                    <?= (isset($livroEdit->status) && $livroEdit->status === $option) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="acoes-formulario">
        <button type="submit" class="<?= ($livroEdit && $livroEdit->id !== null) ? 'btn-editar' : 'btn-adicionar' ?>">
            <?= ($livroEdit && $livroEdit->id !== null) ? 'Atualizar Livro' : 'Adicionar Livro' ?>
        </button>
        <?php if ($livroEdit && $livroEdit->id !== null): ?>
            <a href="index.php" class="btn-cancelar">Cancelar</a>
        <?php endif; ?>
    </div>
</form>

<h2><em>Acervo literário disponível</em></h2>

<table class="tabela-livros">
    <thead>
    <tr>
        <th>Título</th>
        <th>Autor</th>
        <th>Editora</th>
        <th>Quantidade</th>
        <th>Preço (R$)</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($livros)): ?>
        <?php foreach ($livros as $livro): ?>
            <tr>
                <td><?= htmlspecialchars($livro->titulo, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($livro->autor, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($livro->editora, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$livro->quantidade ?></td>
                <td><?= number_format($livro->preco, 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($livro->status, ENT_QUOTES, 'UTF-8') ?></td>
                <td class="acoes-tabela">
                    <a href="?editar=<?= (int)$livro->id ?>" class="btn-editar">Editar</a>
                    <a href="?excluir=<?= (int)$livro->id ?>" class="btn-excluir"
                       onclick="return confirm('Tem certeza que deseja excluir este livro?');">
                        Excluir
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7">Nenhum livro cadastrado.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<footer>
    &copy; <?= date('Y') ?> Livraria INK — todos os direitos reservados.
</footer>
</body>
</html>