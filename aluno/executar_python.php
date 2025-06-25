<!--\projetor\aluno\executar_python.php-->
<!--serve para responder a exercicios onde é precisa criar e compilar código-->
<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
  header("Location: /projetor/index.html");
  exit;
}

$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];
$id_utilizador = $_SESSION['id_utilizador'];

$exercicio_id = $_GET['id'] ?? null;
if (!$exercicio_id) {
  exit("Exercício inválido.");
}

try {
  $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $conn->prepare("SELECT * FROM exercicios WHERE id = ?");
  $stmt->execute([$exercicio_id]);
  $exercicio = $stmt->fetch(PDO::FETCH_ASSOC);

  $stmt = $conn->prepare("SELECT * FROM perguntas WHERE id_exercicio = ?");
  $stmt->execute([$exercicio_id]);
  $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $stmt = $conn->prepare("SELECT url FROM videos WHERE id_exercicio = ?");
  $stmt->execute([$exercicio_id]);
  $videos = $stmt->fetchAll(PDO::FETCH_COLUMN);

  $stmt = $conn->prepare("SELECT url FROM imagens WHERE id_exercicio = ?");
  $stmt->execute([$exercicio_id]);
  $imagens = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
  exit("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Executar Código Python</title>
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/dashboard_aluno.css">
  <link rel="stylesheet" href="/projetor/css/responder_exercicio.css">
  <link rel="stylesheet" href="/projetor/css/executar_python.css">
  <script src="https://cdn.jsdelivr.net/pyodide/v0.23.4/full/pyodide.js"></script>
</head>
<body>
  <header class="header-principal">
    <div class="header-esquerda">
      <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
      <h1>Instituto de Educação</h1>
    </div>
    <div class="header-centro">
      <h1>Executar Exercício</h1>
    </div>
    <div class="user-info">
      <p><strong><?= htmlspecialchars($nome) ?></strong></p>
      <p><em><?= $tipo ?></em></p>
      <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
    </div>
  </header>

  <div class="layout-flex">
    <nav class="sidebar">
      <h2>Menu</h2>
      <a class="menu-button" href="dashboard_aluno.php">Dashboard</a> <br><br>
      <a class="menu-button" href="listar_todos_exercicios.php">Exercícios</a>
    </nav>

    <main class="conteudo">
      <h2><?= htmlspecialchars($exercicio['titulo']) ?></h2>
      <p><?= nl2br(htmlspecialchars($exercicio['descricao'])) ?></p>

      <?php if ($videos): ?>
        <h3>Vídeos Associados</h3>
        <div class="videos-bloco">
          <?php foreach ($videos as $url): ?>
            <?php
              $isExternal = str_starts_with($url, 'http');
              $videoSrc = $isExternal ? $url : '/projetor' . $url;
            ?>
            <?php if ($isExternal): ?>
              <iframe class="video-frame" src="<?= htmlspecialchars($videoSrc) ?>" allowfullscreen></iframe>
            <?php else: ?>
              <video class="video-frame" controls>
                <source src="<?= htmlspecialchars($videoSrc) ?>" type="video/mp4">
                O seu navegador não suporta vídeos.
              </video>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($imagens): ?>
        <h3>Imagens Associadas</h3>
        <div class="imagens-bloco">
          <?php foreach ($imagens as $img): ?>
            <?php
              $isExternal = str_starts_with($img, 'http');
              $imgSrc = $isExternal ? $img : '/projetor/' . $img;
            ?>
            <a href="<?= htmlspecialchars($imgSrc) ?>" target="_blank">
              <img src="<?= htmlspecialchars($imgSrc) ?>" class="imagem-click" alt="Imagem do exercício">
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form onsubmit="event.preventDefault(); avaliarRespostas();">
        <?php foreach ($perguntas as $i => $p): ?>
          <div class="pergunta-item">
            <label for="resposta<?= $i ?>">
              <strong>Pergunta <?= $i + 1 ?>:</strong> <?= htmlspecialchars($p['texto']) ?>
            </label>
            <textarea id="resposta<?= $i ?>" data-esperado="<?= htmlspecialchars(trim($p['resposta'])) ?>" rows="11"></textarea>
            <p id="feedback<?= $i ?>" class="feedback"></p>
            <pre id="output<?= $i ?>" style="background:#f4f4f4; padding:10px; border-radius:6px;"></pre>
          </div>
        <?php endforeach; ?>
        <button type="submit" class="btn-submeter">Executar e Avaliar</button>
        <div id="resultado-final" class="sucesso"></div>
      </form>
    </main>
  </div>

  <footer>
    <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
  </footer>

  <script src="/projetor/js/executar_python.js"></script>
</body>
</html>
