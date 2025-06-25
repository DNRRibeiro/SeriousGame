<!---\projetor\admin\editar_exercicio.php--->
<!--serve para ediar exercicios, já criados-->
<?php
session_start();
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

if (!isset($_GET['id'])) {
    exit("ID do exercício não fornecido.");
}

$id_exercicio = (int) $_GET['id'];
$mensagem = "";

try {
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM exercicios WHERE id = ?");
    $stmt->execute([$id_exercicio]);
    $exercicio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exercicio) exit("Exercício não encontrado.");

    $familiaStmt = $conn->query("SELECT id, nome FROM familias ORDER BY nome");
    $familias = $familiaStmt->fetchAll(PDO::FETCH_ASSOC);

    $perguntas = $conn->prepare("SELECT * FROM perguntas WHERE id_exercicio = ?");
    $perguntas->execute([$id_exercicio]);
    $lista_perguntas = $perguntas->fetchAll(PDO::FETCH_ASSOC);

    $imgStmt = $conn->prepare("SELECT url FROM imagens WHERE id_exercicio = ?");
    $imgStmt->execute([$id_exercicio]);
    $imagens = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

    $vidStmt = $conn->prepare("SELECT url FROM videos WHERE id_exercicio = ?");
    $vidStmt->execute([$id_exercicio]);
    $videos = $vidStmt->fetchAll(PDO::FETCH_COLUMN);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $ordem = (int) ($_POST['ordem'] ?? 1);
        $id_familia = (int) ($_POST['id_familia'] ?? 0);
        $perguntas = $_POST['perguntas'] ?? [];
        $respostas = $_POST['respostas'] ?? [];
        $pontos = $_POST['pontos'] ?? [];
        $videoUrls = $_POST['videos'] ?? [];
        $imagemUrls = $_POST['imagens'] ?? [];

        $conn->prepare("UPDATE exercicios SET titulo = ?, descricao = ?, ordem = ?, id_familia = ? WHERE id = ?")
             ->execute([$titulo, $descricao, $ordem, $id_familia, $id_exercicio]);

        $conn->prepare("DELETE FROM perguntas WHERE id_exercicio = ?")->execute([$id_exercicio]);
        $pStmt = $conn->prepare("INSERT INTO perguntas (id_exercicio, texto, resposta, pontos) VALUES (?, ?, ?, ?)");
        foreach ($perguntas as $i => $texto) {
            $pStmt->execute([$id_exercicio, $texto, $respostas[$i] ?? '', $pontos[$i] ?? 1]);
        }

        $conn->prepare("DELETE FROM imagens WHERE id_exercicio = ?")->execute([$id_exercicio]);
        $iStmt = $conn->prepare("INSERT INTO imagens (id_exercicio, url) VALUES (?, ?)");
        foreach ($imagemUrls as $url) {
            if (!empty(trim($url))) {
                $iStmt->execute([$id_exercicio, $url]);
            }
        }

        $conn->prepare("DELETE FROM videos WHERE id_exercicio = ?")->execute([$id_exercicio]);
        $vStmt = $conn->prepare("INSERT INTO videos (id_exercicio, url) VALUES (?, ?)");
        foreach ($videoUrls as $url) {
            if (!empty(trim($url))) {
                $vStmt->execute([$id_exercicio, $url]);
            }
        }

        $mensagem = "<p class='sucesso'>Exercício atualizado com sucesso!</p>";
    }
} catch (PDOException $e) {
    $mensagem = "<p class='erro'>Erro: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Editar Exercício</title>
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/formulario.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
</head>
<body>
<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" class="logo-header" alt="Logotipo">
    <h1>Instituto de Educação</h1>
  </div>
  <div class="header-centro">
    <h1>Editar Exercício</h1>
  </div>
  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<div class="layout-flex">
  <nav>
    <h2>Menu</h2>
    <ul>
      <li><a href="/projetor/admin/listar_exercicios.php" class="menu-button">⬅ Voltar ao Painel</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <?= $mensagem ?>
    <form method="post" class="formulario" enctype="multipart/form-data">
      <label>Título:</label>
      <input type="text" name="titulo" value="<?= htmlspecialchars($exercicio['titulo']) ?>" required>

      <label>Família:</label>
      <select name="id_familia" required>
        <?php foreach ($familias as $f): ?>
          <option value="<?= $f['id'] ?>" <?= $f['id'] == $exercicio['id_familia'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($f['nome']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>Descrição:</label>
      <textarea name="descricao" rows="4"><?= htmlspecialchars($exercicio['descricao']) ?></textarea>

      <label>Posição (ordem):</label>
      <input type="number" name="ordem" value="<?= $exercicio['ordem'] ?>" min="1" required>

      <label>Perguntas:</label>
      <div id="perguntas-container">
        <?php foreach ($lista_perguntas as $p): ?>
          <div class="pergunta-item">
            <input type="text" name="perguntas[]" value="<?= htmlspecialchars($p['texto']) ?>" required>
            <textarea name="respostas[]" placeholder="Resposta" rows="11" required><?= htmlspecialchars($p['resposta']) ?></textarea>
            <input type="number" name="pontos[]" value="<?= $p['pontos'] ?>" min="1" required>
          </div>
        <?php endforeach; ?>
      </div>
      <br>
      <button type="button" id="btn-adicionar-pergunta">+ Adicionar Pergunta</button>
      <br><br>
      <label>Vídeos (URL local ou do YouTube):</label>
      <?php foreach ($videos as $url): ?>
        <input type="text" name="videos[]" value="<?= htmlspecialchars($url) ?>">
      <?php endforeach; ?>
      <input type="text" name="videos[]" placeholder="/projetor/videos/... ou https://youtube.com/...">

      <label>Imagens (URL de arquivo existente):</label>
      <?php foreach ($imagens as $url): ?>
        <input type="text" name="imagens[]" value="<?= htmlspecialchars($url) ?>">
      <?php endforeach; ?>
      <input type="text" name="imagens[]" placeholder="/projetor/imagens/...">

      <label>Upload de Imagens (1920x1080):</label>
      <input type="file" name="imagem_upload[]" accept="image/*" multiple>

      <br><br>
      <button type="submit">Guardar Alterações</button>
    </form>
  </main>
</div>

<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>

<script src="/projetor/js/valida_imagem.js"></script>
<script src="/projetor/js/adicionar_pergunta.js"></script>
</body>
</html>
