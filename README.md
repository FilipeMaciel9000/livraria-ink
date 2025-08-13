# 📚 Livraria INK – Sistema CRUD para Controle de Estoque

[![Tecnologia: PHP](https://img.shields.io/badge/PHP-8.2-blueviolet?logo=php)](https://www.php.net/)
[![Banco de Dados: MySQL](https://img.shields.io/badge/MySQL-8.0-orange?logo=mysql)](https://www.mysql.com/)

## 📖 Descrição

**Livraria INK** é um sistema web desenvolvido como parte de uma avaliação acadêmica, utilizando **PHP orientado a objetos** no padrão **MVC (Model-View-Controller)**.
O projeto oferece uma solução robusta para **gestão de estoque de livros**, com operações completas **CRUD** (Criar, Ler, Atualizar e Excluir).

A aplicação permite que **administradores autenticados** cadastrem, editem, visualizem e removam livros, bem como controlem a quantidade disponível.
A interface é simples, responsiva e compatível com navegadores modernos, com interatividade opcional via JavaScript.

---

## ✨ Funcionalidades

- **Autenticação de Usuários**

  - Login seguro com `password_hash()` e validação de credenciais.
  - Middleware para impedir acesso não autorizado.
  - Recuperação de senha prevista para versões futuras.

- **Gerenciamento de Livros**

  - Filtro por **título**, **autor**, **editora** e **status**.
  - Cadastro, edição e exclusão de registros.
  - Ordenação por título, preço ou quantidade.

- **Controle de Estoque**

  - Atualização automática das quantidades após cada operação CRUD.
  - Alertas visuais para estoque baixo (menos de 5 unidades).

- **Segurança**

  - Proteção **CSRF** com tokens únicos.
  - **Prepared Statements** para evitar SQL Injection.
  - Sanitização de entradas contra XSS (`htmlspecialchars()`).

- **Registro de Atividades**

  - Logs de login, logout e operações CRUD na tabela `atividades`.

- **Responsividade**

  - Layout adaptado para desktop e mobile com **Bootstrap 5**.

---

## 📂 Estrutura do Projeto

```
livraria-ink/
├── index.php                # Controlador principal
├── login.php                # Tela de login
├── assets/                  # Arquivos estáticos
│   ├── css/                 # Estilos
│   ├── js/                  # Scripts opcionais
│   └── images/              # Imagens do sistema
├── models/                  # Modelos e acesso a dados
├── utils/                   # Utilitários e configurações
├── controllers/             # Controladores de lógica
├── views/                   # Interfaces do usuário
└── README.md                # Documentação
```

---

## 🛠 Tecnologias

- **PHP 8.2.12** – Backend orientado a objetos.
- **MySQL 8.0** – Banco de dados relacional.
- **Bootstrap 5** – Estilização e responsividade.
- **HTML5 & CSS3** – Estrutura e design.
- Ambiente local: XAMPP, WAMP ou similar.

---

## ⚙️ Pré-requisitos

- Servidor web com **PHP 8.2+** (Apache recomendado).
- **MySQL** ou **MariaDB**.
- Navegador moderno.
- Internet para carregamento de dependências via CDN.

---

## ▶️ Execução do Projeto

1. **Instalar Ambiente**

   - Configure XAMPP, WAMP ou similar.

2. **Banco de Dados**

   - Crie o banco `livraria_ink`.
   - Importe o arquivo `livraria_ink.sql`.
   - Configure `utils/conexao.php` com suas credenciais.

3. **Rodar o Sistema**

   - Coloque o projeto no diretório raiz (`htdocs`).
   - Inicie Apache e MySQL.
   - Acesse `http://localhost/livraria-ink`.

---

## 🗄 Estrutura do Banco de Dados

- **usuarios** → credenciais de acesso.
- **livros** → catálogo de livros.
- **atividades** → histórico de ações.

---

## ✅ Boas Práticas Implementadas

- Arquitetura **MVC**.
- Validação e sanitização de dados.
- Segurança contra **CSRF**, **SQL Injection** e **XSS**.
- Código modular com classes específicas.
- Logs de atividades para auditoria.

---

## 🚀 Melhorias Futuras

- Filtros avançados de busca.
- Relatórios em PDF/Excel.
- API REST para integração externa.
- Autenticação multifator.
- Rate limiting em tentativas de login.

---

## 📝 Licença

Projeto sob licença [MIT](LICENSE).
