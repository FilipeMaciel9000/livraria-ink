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
        /* Estilos específicos para a página de login */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .login-header {
            margin-bottom: 2rem;
        }
        
        .login-header img {
            width: 60px;
            margin-bottom: 1rem;
        }
        
        .login-header h1 {
            color: #333;
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
        }
        
        .login-header p {
            color: #666;
            margin: 0;
            font-style: italic;
        }
        
        .login-form .campo-form {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .login-form input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .login-form input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .login-btn {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s ease;
            margin-top: 1rem;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            margin-bottom: 1.5rem;
            padding: 0.8rem;
            border-radius: 5px;
            text-align: center;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-info {
            background-color: #cce7ff;
            color: #004085;
            border: 1px solid #99d3ff;
        }
        
        .footer-login {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/images/ink.png" alt="Logo Livraria INK">
                <h1>Livraria INK</h1>
                <p>Acesso ao Sistema</p>
            </div>

            <?php if ($mensagem): ?>
                <div class="alert <?= htmlspecialchars($tipoMensagem, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                
                <div class="campo-form">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                </div>

                <div class="campo-form">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                </div>

                <button type="submit" class="login-btn">
                    Entrar no Sistema
                </button>
            </form>

            <div class="footer-login">
                <p>&copy; <?= date('Y') ?> Livraria INK</p>
                <p>Sistema de Gerenciamento de Estoque</p>
            </div>
        </div>
    </div>
</body>
</html>