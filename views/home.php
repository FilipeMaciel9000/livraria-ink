<!-- HTML -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="author" content="Filipe Maciel" />
    <meta name="description" content="Sistema de gerenciamento de estoque para livrarias." />
    <title>Livraria INK</title>
    <link rel="icon" href="assets/images/ink.png" type="image/x-icon" />
    <style>
                /* Reset básico */
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        }

        body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        color: #333;
        padding: 20px;
        }

        /* Cabeçalho */
        header {
        text-align: center;
        margin-bottom: 30px;
        }

        header img {
        width: 80px;
        height: auto;
        }

        header h1 {
        font-size: 2em;
        margin-top: 10px;
        color: #2c3e50;
        }

        header h2 {
        font-size: 1.2em;
        color: #7f8c8d;
        }

        /* Mensagens de feedback */
        .alert {
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-weight: bold;
        }

        .alert-success {
        background-color: #d4edda;
        color: #155724;
        }

        .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        }

        /* Formulário principal */
        .formulario-livro {
        background-color: #ffffff;
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 8px;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }

        .campo-form {
        margin-bottom: 15px;
        }

        .campo-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        }

        .campo-form input,
        .campo-form select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        }

        .acoes-formulario {
        margin-top: 10px;
        display: flex;
        justify-content: flex-end;
        }

        .acoes-formulario button,
        .acoes-formulario .btn-cancelar {
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        font-weight: bold;
        text-decoration: none;
        cursor: pointer;
        }

        .btn-adicionar {
        background-color: #27ae60;
        color: white;
        margin-left: auto; /* empurra para a direita */
        display: block;
        }

        .btn-editar {
        background-color: #2980b9;
        color: white;
        }

        .btn-cancelar {
        margin-left: 15px;
        color: #c0392b;
        background: transparent;
        }

        /* Filtro de busca */
        .filtro-busca {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
        align-items: center;
        }

        .filtro-busca input,
        .filtro-busca select {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        flex: 1;
        min-width: 180px;
        }

        .btn-filtrar,
        .btn-limpar {
        padding: 8px 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        }

        .btn-filtrar {
        background-color: #2980b9;
        color: white;
        }

        .btn-limpar {
        background-color: #bdc3c7;
        color: #2c3e50;
        text-decoration: none;
        }

        /* Tabela de livros */
        .tabela-livros {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
        }

        .tabela-livros th,
        .tabela-livros td {
        padding: 12px;
        border-bottom: 1px solid #ecf0f1;
        text-align: left;
        }

        .tabela-livros th {
        background-color: #ecf0f1;
        color: #2c3e50;
        }

        .tabela-livros tr:last-child td {
        border-bottom: none;
        }

        .acoes-tabela {
        display: flex;
        gap: 10px;
        }

        .acoes-tabela a {
        padding: 6px 10px;
        font-size: 0.9em;
        text-decoration: none;
        border-radius: 4px;
        font-weight: bold;
        }

        .acoes-tabela .btn-editar {
        background-color: #3498db;
        color: white;
        }

        .acoes-tabela .btn-excluir {
        background-color: #e74c3c;
        color: white;
        }

        /* Rodapé */
        footer {
        text-align: center;
        margin-top: 40px;
        color: #95a5a6;
        font-size: 0.9em;
        }

        .header-main {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logo-section {
        display: flex;
        align-items: center;
        gap: 15px;
        }

        .logo-section img {
        width: 50px;
        height: 50px;
        filter: brightness(0) invert(1);
        }

        .logo-section h1 {
        color: white;
        margin: 0;
        font-size: 1.8rem;
        }

        .logo-section h2 {
        color: rgba(255, 255, 255, 0.8);
        margin: 0;
        font-size: 1rem;
        }

        .user-section {
        display: flex;
        align-items: center;
        gap: 20px;
        }

        .user-info {
        text-align: right;
        }

        .user-name {
        display: block;
        font-weight: bold;
        font-size: 1.1rem;
        margin-bottom: 3px;
        }

        .user-role {
        font-size: 0.85rem;
        opacity: 0.8;
        background: rgba(255, 255, 255, 0.2);
        padding: 2px 8px;
        border-radius: 12px;
        }

        .btn-logout {
        background: rgba(220, 53, 69, 0.9);
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 2px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(5px);
        }

        .btn-logout:hover {
        background: rgba(220, 53, 69, 1);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        text-decoration: none;
        color: white;
        border-color: rgba(255, 255, 255, 0.5);
        }

        .info-bar {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px 20px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        }

        .info-item {
        display: flex;
        align-items: center;
        gap: 5px;
        }

        .status-online {
        color: #28a745;
        font-weight: bold;
        }

        /* Responsive */
        @media (max-width: 768px) {
        .header-main {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }

        .user-section {
            width: 100%;
            justify-content: center;
        }

        .info-bar {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }

        .logo-section {
            justify-content: center;
        }
        }

        @media (max-width: 480px) {
        .user-section {
            flex-direction: column;
            gap: 10px;
        }

        .user-info {
            text-align: center;
        }

        .btn-logout {
            width: 100%;
            text-align: center;
        }
        }
    </style>
</head>
<body>
<header>
    <div class="header-main">
        <div class="logo-section">
            <div>
                <h1>Livraria INK</h1>
                <h2><em>Gerenciamento de estoque</em></h2>
            </div>
        </div>
        
        <div class="user-section">
            <div class="user-info">
                <span class="user-name">👤 <?= htmlspecialchars($usuarioLogado['nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="user-role"><?= ucfirst($usuarioLogado['tipo'] ?? 'funcionario') ?></span>
            </div>
            
            <div class="user-actions">
                <a href="?logout=1" class="btn-logout" onclick="return confirm('⚠️ Tem certeza que deseja sair do sistema?');">
                    🚪 Sair
                </a>
            </div>
        </div>
    </div>    
</header>

<nav class="info-bar">
    <div class="info-item">
        <strong>📧 Email:</strong> <?= htmlspecialchars($usuarioLogado['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
    </div>
    <div class="info-item">
        <strong>🕒 Último acesso:</strong> <?= date('d/m/Y H:i') ?>
    </div>
    <div class="info-item">
        <strong>📊 Status:</strong> <span class="status-online">Online</span>
    </div>
</nav>

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
<form method="GET" action="" class="filtro-busca">
    <input type="text" name="busca" placeholder="Buscar por título ou autor..."
           value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>" />
    <select name="status">
        <option value="">Todos os status</option>
        <?php foreach (Livro::STATUS_VALIDOS as $option): ?>
            <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"
                <?= $statusFiltro === $option ? 'selected' : '' ?>>
                <?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-filtrar">Filtrar</button>
    <a href="<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?'), ENT_QUOTES, 'UTF-8') ?>" class="btn-limpar">Limpar</a>
</form>

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
            <td colspan="7"><?= empty($busca) && empty($statusFiltro) 
                ? 'Nenhum livro cadastrado.' 
                : 'Nenhum livro encontrado com os filtros aplicados.' ?></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<footer>
    &copy; <?= date('Y') ?> Livraria INK — todos os direitos reservados.
</footer>
</body>
</html>