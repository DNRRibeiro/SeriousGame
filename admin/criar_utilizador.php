<!---\projetor\admin\criar_utilizador.php--->
<!--serve para criar os utizadores-->
<?php
// Início da sessão para aceder às variáveis de sessão
session_start();

// Verifica se o utilizador tem permissões de administrador
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: /projetor/index.html");
    exit;
}

// Guarda o tipo e o email do utilizador logado
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

// Variável para guardar mensagens de feedback
$mensagem = "";

// Processamento do formulário após submissão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recolha dos dados do formulário com valores por omissão
    $nome_utilizador = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $tipo_utilizador = $_POST['tipo'] ?? 'aluno';
    $turma = $_POST['turma'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $instituicao = $_POST['instituicao'] ?? '';

    try {
        // Conexão à base de dados
        $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Inserção do novo utilizador na base de dados
        $stmt = $conn->prepare("INSERT INTO utilizadores (nome, email, pass, tipo, turma, ano, instituicao)
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome_utilizador, $email, $pass, $tipo_utilizador, $turma, $ano, $instituicao]);

        // Mensagem de sucesso
        $mensagem = "<p style='color:green;'>✅ Utilizador criado com sucesso!</p>";
    } catch (PDOException $e) {
        // Mensagem de erro em caso de falha
        $mensagem = "<p style='color:red;'>Erro ao criar utilizador: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Criar Utilizador</title>
  <!-- Inclusão de folhas de estilo -->
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/formulario.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
</head>
<body>
  <!-- Cabeçalho da aplicação -->
  <header class="header-principal">
    <div class="header-esquerda">
      <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
      <h1>Instituto de Educação</h1>
    </div>

    <div class="header-centro">
      <h1>Adicionar novo utilizador</h1>
    </div>

    <!-- Informações do utilizador atual -->
     <!--serve para criar os utilizadores-->
    <div class="user-info">
      <p><strong><?= htmlspecialchars($nome) ?></strong></p>
      <p><em><?= $tipo ?></em></p>
      <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
    </div>
  </header>

  <!-- Corpo principal com painel de administração -->
  <div class="painel-admin">
    <!-- Navegação lateral -->
    <nav>
      <ul>
        <li><a href="/projetor/admin/painel.php">⬅ Voltar ao Painel</a></li>
        <li><a href="/projetor/admin/gerir_utilizadores.php"> Gerir Utilizadores</a></li>
      </ul>
    </nav>

    <!-- Conteúdo principal -->
    <main class="conteudo">
      <!-- Mensagem de feedback -->
      <?= $mensagem ?>

      <!-- Formulário para criação de utilizador -->
      <form method="POST">
        <label>Nome:</label><br>
        <input type="text" name="nome" required><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br>

        <label>Palavra-passe:</label><br>
        <input type="password" name="pass" required><br>

        <label>Tipo:</label><br>
        <select name="tipo">
          <option value="aluno">Aluno</option>
          <option value="professor">Professor</option>
          <option value="admin">Administrador</option>
        </select><br>

        <label>Turma:</label><br>
        <input type="text" name="turma"><br>

        <label>Ano:</label><br>
        <input type="text" name="ano"><br>

        <label>Instituição:</label><br>
        <input type="text" name="instituicao"><br><br>

        <button type="submit" class="menu-button">Criar Utilizador</button>
      </form>
    </main>
  </div>

  <!-- Rodapé da aplicação -->
  <footer>
    <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
  </footer>
</body>
</html>
