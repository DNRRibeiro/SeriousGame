<!---\projetor\admin\ver_exercicio.php--->
<?php
// Inicia a sessão para aceder às variáveis da sessão
session_start();

// Obtém o tipo e email do utilizador autenticado
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

// Garante que apenas utilizadores autenticados podem aceder
if (!isset($_SESSION['tipo'])) {
    header("Location: /projetor/index.html");
    exit;
}

// Verifica se foi fornecido um ID de exercício por GET
$id = $_GET['id'] ?? null;
if (!$id) {
    exit("ID do exercício não especificado.");
}

try {
    // Conexão com a base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca o exercício pelo ID fornecido
    $stmt = $conn->prepare("SELECT * FROM exercicios WHERE id = ?");
    $stmt->execute([$id]);
    $exercicio = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se o exercício existe
    if (!$exercicio) {
        exit("Exercício não encontrado.");
    }

    // Busca todas as perguntas associadas ao exercício
    $perguntas = $conn->prepare("SELECT * FROM perguntas WHERE id_exercicio = ?");
    $perguntas->execute([$id]);
    $perguntas = $perguntas->fetchAll(PDO::FETCH_ASSOC);

    // Busca todos os vídeos associados ao exercício
    $videos = $conn->prepare("SELECT * FROM videos WHERE id_exercicio = ?");
    $videos->execute([$id]);
    $videos = $videos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Ver Exercício</title>
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
    <h1>Visualizar Exercício</h1>
  </div>

  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<div class="layout-flex">
  <nav>
    <ul>
      <?php if ($_SESSION['tipo'] === 'aluno'): ?>
        <li><a href="/projetor/aluno/painel_aluno.php">⬅ Voltar</a></li>
      <?php else: ?>
        <li><a href="/projetor/painel.php">⬅ Voltar</a></li>
      <?php endif; ?>
    </ul>
  </nav>

  <main class="conteudo">
    <!-- Descrição do exercício -->
    <p><?= nl2br(htmlspecialchars($exercicio['descricao'])) ?></p>

    <!-- Lista de perguntas -->
    <h3>Perguntas e Respostas</h3>
    <?php foreach ($perguntas as $i => $p): ?>
      <div class="pergunta-item">
        <p><strong>Pergunta <?= $i + 1 ?>:</strong> <?= htmlspecialchars($p['texto']) ?></p>
        <p><em>Resposta:</em> <?= htmlspecialchars($p['resposta']) ?> | <strong><?= $p['pontos'] ?> ponto(s)</strong></p>
      </div>
    <?php endforeach; ?>

    <!-- Vídeos do exercício -->
    <?php if (count($videos) > 0): ?>
      <h3>Vídeos</h3>
      <?php foreach ($videos as $v): ?>
        <video width="480" height="320" controls>
          <source src="<?= htmlspecialchars($v['url']) ?>" type="video/mp4">
          O seu navegador não suporta a reprodução de vídeo.
        </video>
        <br><br>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>

<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>
</body>
</html>
