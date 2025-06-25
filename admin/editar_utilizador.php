<!---\projetor\admin\editar_utilizador.php--->
<!--serve para editar os utilizadores dos já criadas-->
<?php
session_start();

// Verifica se o utilizador tem permissões de administrador
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: /projetor/index.html");
    exit;
}

$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];
$mensagem = "";

// Verifica se o ID foi passado
if (!isset($_GET['id'])) {
    die("ID do utilizador não fornecido.");
}

$id_utilizador = intval($_GET['id']);

try {
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Se o formulário foi submetido
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome_utilizador = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $nova_pass = $_POST['pass'] ?? '';
        $tipo_utilizador = $_POST['tipo'] ?? 'aluno';
        $turma = $_POST['turma'] ?? '';
        $ano = $_POST['ano'] ?? '';
        $instituicao = $_POST['instituicao'] ?? '';

        // Atualiza os dados com ou sem password
        if (!empty($nova_pass)) {
            $stmt = $conn->prepare("UPDATE utilizadores SET nome = ?, email = ?, pass = ?, tipo = ?, turma = ?, ano = ?, instituicao = ? WHERE id = ?");
            $stmt->execute([$nome_utilizador, $email, $nova_pass, $tipo_utilizador, $turma, $ano, $instituicao, $id_utilizador]);
        } else {
            $stmt = $conn->prepare("UPDATE utilizadores SET nome = ?, email = ?, tipo = ?, turma = ?, ano = ?, instituicao = ? WHERE id = ?");
            $stmt->execute([$nome_utilizador, $email, $tipo_utilizador, $turma, $ano, $instituicao, $id_utilizador]);
        }

        $mensagem = "<p style='color:green;'>✅ Utilizador atualizado com sucesso!</p>";
    }

    // Obtem os dados atuais do utilizador
    $stmt = $conn->prepare("SELECT * FROM utilizadores WHERE id = ?");
    $stmt->execute([$id_utilizador]);
    $utilizador = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$utilizador) {
        die("Utilizador não encontrado.");
    }

} catch (PDOException $e) {
    die("Erro de base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Editar Utilizador</title>
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/formulario.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
</head>
<body>
  <header class="header-principal">
    <div class="header-esquerda">
      <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
      <h1>Instituto de Educação</h1>
    </div>
    <div class="header-centro">
      <h1>Editar Utilizador</h1>
    </div>
    <div class="user-info">
      <p><strong><?= htmlspecialchars($nome) ?></strong></p>
      <p><em><?= $tipo ?></em></p>
      <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
    </div>
  </header>

  <div class="painel-admin">
    <nav>
      <ul>
        <li><a href="/projetor/admin/gerir_utilizadores.php">⬅ Voltar à Gestão</a></li>
      </ul>
    </nav>

    <main class="conteudo">
      <?= $mensagem ?>

      <form method="POST">
        <label>Nome:</label><br>
        <input type="text" name="nome" value="<?= htmlspecialchars($utilizador['nome']) ?>" required><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($utilizador['email']) ?>" required><br>

        <label>Nova Palavra-passe (opcional):</label><br>
        <input type="password" name="pass" placeholder="Deixe vazio para manter"><br>

        <label>Tipo:</label><br>
        <select name="tipo">
          <option value="aluno" <?= $utilizador['tipo'] === 'aluno' ? 'selected' : '' ?>>Aluno</option>
          <option value="professor" <?= $utilizador['tipo'] === 'professor' ? 'selected' : '' ?>>Professor</option>
          <option value="admin" <?= $utilizador['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
        </select><br>

        <label>Turma:</label><br>
        <input type="text" name="turma" value="<?= htmlspecialchars($utilizador['turma']) ?>"><br>

        <label>Ano:</label><br>
        <input type="text" name="ano" value="<?= htmlspecialchars($utilizador['ano']) ?>"><br>

        <label>Instituição:</label><br>
        <input type="text" name="instituicao" value="<?= htmlspecialchars($utilizador['instituicao']) ?>"><br><br>

        <button type="submit" class="menu-button">Guardar Alterações</button>
      </form>
    </main>
  </div>

  <footer>
    <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
  </footer>
</body>
</html>
