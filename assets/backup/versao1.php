<?php
// Livraria INK - Sistema de gerenciamento de estoque de livros
// Autor: Filipe Maciel
// Data: 07/03/2025
// Descrição: Aplicação simples em PHP para controle de estoque de livros, com operações CRUD e persistência em banco de dados MySQL.
// Versão: 1.0 — Primeira versão funcional com interface web, persistência e uso básico de prepared statements, paradigma de programção procedural.
//
// PONTOS POSITIVOS DESTA VERSÃO:
// - Implementa operações CRUD (Create, Read, Update, Delete) de forma funcional.
// - Usa prepared statements, o que evita injeções SQL (mitigação básica de segurança).
// - Apresenta layout limpo e estilizado com CSS customizado.
// - Utiliza htmlspecialchars para evitar XSS na maioria das saídas.
// - Formatação e estrutura de código razoavelmente clara, facilitando manutenção inicial.
// - Usa header() para garantir o charset UTF-8.
//
// LIMITAÇÕES E OPORTUNIDADES DE MELHORIA:
// 1) Validação de entrada limitada — não impede campos com espaços em branco ou strings inválidas.
// 2) Ausência de mensagens de erro ou feedback ao usuário nas operações (ex: falha de DB ou formulário incompleto).
// 3) Nenhuma verificação contra CSRF, expondo o sistema a requisições maliciosas.
// 4) Design responsivo básico, ainda pode apresentar problemas em dispositivos móveis menores.
// 5) CSS está embutido no HTML/PHP, o que compromete a separação de responsabilidades (violação do princípio de separação de camadas).
// 6) A listagem de livros não tem paginação nem filtros — o que compromete a escalabilidade em grandes volumes de dados.
// 7) Segurança contra XSS é parcial: algumas saídas ainda podem escapar sem proteção.
// 8) Uso misto de MySQLi procedural e orientado a objetos, o que torna o código inconsistente.
// 9) Não há separação clara entre lógica de apresentação, controle e acesso a dados — não segue padrão MVC.
// 10) Não há testes automatizados, nem estrutura para testes futuros.
// 11) Não possui versionamento interno (como changelog automatizado).
// 12) Não há sistema de logs para erros ou alterações críticas no banco de dados.
// 13) Comentários no código estão razoavelmente claros, porém não há documentação externa da aplicação.
//
// OBSERVAÇÃO FINAL:
// O sistema representa uma boa base inicial funcional para CRUD simples, mas ainda precisa de melhorias significativas em termos de segurança, arquitetura e usabilidade para ser usado em produção ou escalar para usos maiores.

include 'conexao.php';

// Define charset UTF-8 para evitar problemas com acentuação
header('Content-Type: text/html; charset=utf-8');

// Inicializa variáveis para uso no formulário (edição)
$tituloEdit = '';
$quantidadeEdit = '';
$precoEdit = '';
$idEdit = null;

// Funções de CRUD

// Inserir ou atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação simples
    $titulo = trim($_POST['titulo'] ?? '');
    $quantidade = intval($_POST['quantidade'] ?? 0);
    $preco = floatval(str_replace(',', '.', $_POST['preco'] ?? 0)); // aceita vírgula ou ponto

    if ($titulo === '' || $quantidade <= 0 || $preco < 0) {
        // Poderia mostrar erro, mas aqui só redirecionamos
        header("Location: index.php");
        exit;
    }

    if (!empty($_POST['id'])) {
        // Atualizar
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE livros SET titulo = ?, quantidade = ?, preco = ? WHERE id = ?");
        if ($stmt === false) {
            die("Erro na preparação da query UPDATE: " . $conn->error);
        }
        $stmt->bind_param("sidi", $titulo, $quantidade, $preco, $id);
    } else {
        // Inserir
        $stmt = $conn->prepare("INSERT INTO livros (titulo, quantidade, preco) VALUES (?, ?, ?)");
        if ($stmt === false) {
            die("Erro na preparação da query INSERT: " . $conn->error);
        }
        $stmt->bind_param("sid", $titulo, $quantidade, $preco);
    }
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit;
}

// Excluir
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM livros WHERE id = ?");
        if ($stmt === false) {
            die("Erro na preparação da query DELETE: " . $conn->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit;
}

// Preparar dados para edição (se houver ?editar=ID)
if (isset($_GET['editar'])) {
    $idEdit = intval($_GET['editar']);
    if ($idEdit > 0) {
        $stmt = $conn->prepare("SELECT titulo, quantidade, preco FROM livros WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $idEdit);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $tituloEdit = $row['titulo'];
                $quantidadeEdit = $row['quantidade'];
                $precoEdit = number_format($row['preco'], 2, ',', ''); // Formata para exibir com vírgula
            }
            $stmt->close();
        }
    }
}

// Listagem
$resultado = $conn->query("SELECT * FROM livros ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Livraria INK</title>
    <!-- CSS antigo! -->
    <style>
        /* Reset e Base */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html {
  font-size: 16px;
}

body {
  background-color: #f6f5f3;
  font-family: 'Merriweather', serif;
  color: #2c2c2c;
  padding: 1.25rem;
  line-height: 1.6;
}

/* Cabeçalho */
header {
  margin-bottom: 2.5rem;
  text-align: center;
}

header img {
  width: 100px;
  margin-bottom: 0.625rem;
  filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
}

/* Ajuste nos tamanhos dos títulos */
h1 {
  font-size: 3rem;
  color: #2d1e2f;
  margin-bottom: 0.5rem;
  text-align: center;
}

h2 {
  font-size: 2rem;
  color: #2d1e2f;
  margin-bottom: 0.5rem;
  text-align: center;
}

p {
  font-style: italic;
  color: #666;
  font-size: 1.1rem;
}

/* Formulário */
form {
  margin: 2.5rem auto;
  display: flex;
  flex-direction: column;
  max-width: 26.25rem;
  gap: 0.9375rem;
  background-color: #ffffff;
  padding: 1.5rem;
  border-radius: 0.625rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

/* Oculta campo hidden sem quebrar layout */
input[type='hidden'] {
  display: none;
}

/* Campos de texto */
input[type='text'],
input[type='number'] {
  padding: 0.875rem;
  border: 1px solid #ccc;
  border-radius: 0.5rem;
  font-size: 1rem;
  background-color: #fcfcfc;
  transition: border-color 0.2s ease;
}

input[type='text']:focus,
input[type='number']:focus {
  border-color: #000;
  outline: 2px solid #000;
}

/* Botão base */
button {
  padding: 0.875rem;
  border: none;
  cursor: pointer;
  font-weight: bold;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: background 0.3s ease, transform 0.2s ease;
}

button:active {
  transform: scale(0.98);
}

/* Botão Adicionar (Preto) */
.btn-adicionar {
  background-color: #000;
  color: #fff;
}

.btn-adicionar:hover {
  background-color: #333;
}

/* Botão Editar (Azul) */
.btn-editar {
  background: linear-gradient(to right, #0077b6, #0096c7);
  color: white;
}

.btn-editar:hover {
  background: linear-gradient(to right, #0096c7, #00b4d8);
}

/* Botão Excluir (Vermelho) */
.btn-excluir {
  background: linear-gradient(to right, #d62828, #e63946);
  color: white;
}

.btn-excluir:hover {
  background: linear-gradient(to right, #e63946, #f94144);
}

/* Tabela */
table {
  margin: 2.5rem auto;
  border-collapse: collapse;
  width: 95%;
  max-width: 50rem;
  border-radius: 0.625rem;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

th,
td {
  padding: 0.875rem 1rem;
  text-align: center;
}

th {
  background-color: #000;
  color: #fff;
  font-weight: 600;
  font-size: 1rem;
}

td {
  background-color: #fff;
  font-size: 0.95rem;
  color: #333;
  border-top: 1px solid #eee;
}

/* Links */
a {
  color: #d62828;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s ease;
}

a:hover {
  color: #9d0208;
  text-decoration: underline;
}

/* Rodapé */
footer {
  margin-top: 3.75rem;
  font-size: 0.875rem;
  color: #aaa;
  text-align: center;
}

/* Responsividade básica */
@media (max-width: 500px) {
  h1,
  h2 {
    font-size: 2rem;
  }

  form {
    padding: 1rem;
  }

  table {
    font-size: 0.9rem;
  }

  th,
  td {
    padding: 0.625rem;
  }
}
    </style>
    <link rel="icon" href="estilo/ink.png" type="image/x-icon" />
</head>
<body>
    <header>
        <img src="estilo/ink.png" alt="Logo Livraria INK" />
        <h1>Livraria INK</h1>
        <h2><em>Gerenciamento de estoque</em></h2>
    </header>

    <form method="POST" action="">
        <!-- Campo oculto para edição -->
        <?php if ($idEdit !== null): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($idEdit) ?>" />
        <?php endif; ?>

        <label for="titulo">Título do livro:</label>
        <input 
            type="text" 
            id="titulo" 
            name="titulo" 
            placeholder="Título do livro" 
            value="<?= htmlspecialchars($tituloEdit) ?>" 
            required
        />
        <br/>
        <label for="quantidade">Quantidade:</label>
        <input 
            type="number" 
            id="quantidade" 
            name="quantidade" 
            placeholder="Quantidade" 
            value="<?= htmlspecialchars($quantidadeEdit) ?>" 
            min="1" 
            required
        />

        <br/>
        <label for="preco">Preço (R$):</label>
        <input 
            type="text" 
            id="preco" 
            name="preco" 
            placeholder="Preço do livro" 
            value="<?= htmlspecialchars($precoEdit) ?>" 
            pattern="^\d+(\,\d{1,2})?$" 
            title="Formato: 0,00" 
            required
        />
        <br>
      <button type="submit" class="<?= $idEdit !== null ? 'btn-editar' : 'btn-adicionar' ?>">
    <?= $idEdit !== null ? 'Atualizar Livro' : 'Adicionar Livro' ?>
</button>
        <?php if ($idEdit !== null): ?>
            <a href="index.php" style="margin-left: 15px; color:#c0392b;">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2><em>Acervo literário disponível</em></h2>
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Quantidade</th>
                <th>Preço (R$)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($livro = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($livro['titulo']) ?></td>
                        <td><?= (int)$livro['quantidade'] ?></td>
                        <td><?= number_format($livro['preco'], 2, ',', '.') ?></td>
                        <td style="display: flex; gap: 10px;">
                            <a href="?editar=<?= (int)$livro['id'] ?>" class="btn-editar" style="padding: 8px 12px; font-size: 0.9em;">Editar</a>
                            <a href="?excluir=<?= (int)$livro['id'] ?>" class="btn-excluir" style="padding: 8px 12px; font-size: 0.9em;"
                               onclick="return confirm('Tem certeza que deseja excluir este livro?');">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">Nenhum livro cadastrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <footer>
        &copy; <?= date('Y') ?> Livraria INK — todos os direitos reservados.
    </footer>
</body>
</html>