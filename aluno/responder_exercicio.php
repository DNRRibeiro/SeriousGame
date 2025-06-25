<!---\aluno\responder_exercicio.php--->
<!---resposta a exercicios--->
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
    if (!$exercicio) exit("Exercício não encontrado.");

    $stmt = $conn->prepare("SELECT * FROM perguntas WHERE id_exercicio = ?");
    $stmt->execute([$exercicio_id]);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT url FROM videos WHERE id_exercicio = ?");
    $stmt->execute([$exercicio_id]);
    $videos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $conn->prepare("SELECT url FROM imagens WHERE id_exercicio = ?");
    $stmt->execute([$exercicio_id]);
    $imagens = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $mensagem = "";
    $feedback_perguntas = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $respostas = $_POST['respostas'] ?? [];
        $pontos_totais = 0;

        foreach ($perguntas as $i => $p) {
            $correta = trim(strtolower($p['resposta']));
            $aluno = trim(strtolower($respostas[$i] ?? ''));
            $pontos = (int)$p['pontos'];

            if ($correta === $aluno) {
                $pontos_totais += $pontos;
                $feedback_perguntas[$i] = [
                    'correto' => true,
                    'pontos' => $pontos,
                    'texto' => "✅ Resposta certa! +$pontos ponto(s)."
                ];
            } else {
                $feedback_perguntas[$i] = [
                    'correto' => false,
                    'texto' => "❌ Resposta errada."
                ];
            }
        }

        $stmt = $conn->prepare("INSERT INTO classificacao (id_utilizador, id_exercicio, pontos, data_hora) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$id_utilizador, $exercicio_id, $pontos_totais]);

        $mensagem = "<p class='sucesso'>✅ Submissão registada! Pontos: <strong>$pontos_totais</strong></p>";
    }

} catch (PDOException $e) {
    exit("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Responder Exercício</title>
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/dashboard_aluno.css">
  <link rel="stylesheet" href="/projetor/css/responder_exercicio.css">
  <script defer src="/projetor/js/progresso_exercicio.js"></script>
</head>
<body class="pagina-flex">

<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
    <h1>Instituto de Educação</h1>
  </div>
  <div class="header-centro">
    <h1>Responder Exercício</h1>
  </div>
  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<div class="dashboard-container">
  <nav class="sidebar">
    <h3>Menu</h3>
    <ul>
      <li><a class="btn-exercicio respondido" href="/projetor/aluno/dashboard_aluno.php">Painel de Controlo</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <?= $mensagem ?>

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

    <form method="POST">
      <?php foreach ($perguntas as $i => $p): ?>
        <div class="pergunta-item">
          <label for="resposta<?= $i ?>">
            <strong>Pergunta <?= $i + 1 ?>:</strong> <?= htmlspecialchars($p['texto']) ?>
          </label>
          <input type="text" id="resposta<?= $i ?>" name="respostas[<?= $i ?>]" placeholder="Sua resposta..." required class="input-resposta">

          <?php if (isset($feedback_perguntas[$i])): ?>
            <p class="feedback <?= $feedback_perguntas[$i]['correto'] ? 'correto' : 'errado' ?>">
              <?= $feedback_perguntas[$i]['texto'] ?>
            </p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <button type="submit" class="btn-submeter">Submeter Respostas</button>
    </form>
  </main>
</div>

<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>

</body>
</html>
