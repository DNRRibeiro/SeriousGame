 <!-- \projetor\inquerito\inquerito.php -->
<?php
session_start();

// Verifica se o utilizador tem sessão válida e é aluno
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: /projetor/index.html");
    exit;
}

$nome = $_SESSION['email'];
$id = $_SESSION['id_utilizador'];

// Conexão à base de dados para verificar se já respondeu ao inquérito
$conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $conn->prepare("SELECT COUNT(*) FROM inqueritos WHERE id_utilizador = ?");
$stmt->execute([$id]);
$jaRespondeu = $stmt->fetchColumn() > 0;
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Inquérito de Avaliação</title>
  <!-- Importação de CSS e definição da variável JS para controlar acesso -->
    <link rel="stylesheet" href="inquerito.css">
    <link rel="stylesheet" href="/projetor/css/header.css">
    <link rel="stylesheet" href="/projetor/css/dash_aluno.css">
  <script>
    const jaRespondeu = <?= $jaRespondeu ? 'true' : 'false' ?>;
  </script>
  <script src="/projetor/js/inquerito.js" defer></script>
</head>
<body>
  <!-- Cabeçalho com logo, título e info do utilizador -->
  <header class="header-principal">
    <div class="header-esquerda">
      <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
      <h1>Instituto de Educação</h1>
    </div>
    <div class="header-centro">
      <h1>Inquérito de Avaliação</h1>
    </div>
    <div class="user-info">
      <p><strong><?= htmlspecialchars($nome) ?></strong></p>
      <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
    </div>
  </header>

  <!-- Layout principal com nav lateral -->
  <div class="layout-flex">
    <nav class="sidebar">
      <h2>Menu</h2>
      <ul>
        <li><a href="/projetor/aluno/dashboard_aluno.php">Voltar ao Dashboard</a></li>
      </ul>
    </nav>

    <main class="conteudo">
      <!-- Mensagem caso o aluno já tenha respondido -->
      <div id="mensagem"></div>

      <!-- Formulário principal do inquérito -->
      <form action="processar_inquerito.php" method="POST" id="inqueritoForm">
        <div id="perguntasContainer" class="lista-inquerito"></div>

        <!-- Campo de texto para comentários adicionais -->
        <div class="lista-inquerito">
          <label for="comentarios">Comentários adicionais:</label>
          <textarea name="comentarios" id="comentarios"></textarea>
        </div>

        <!-- Botão de envio -->
        <button type="submit" id="enviarBtn">Enviar Avaliação</button>
      </form>
    </main>
  </div>

  <footer>
    <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
  </footer>
</body>
</html>