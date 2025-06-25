<!---\projetor\admin\criar_exercicio.php--->
<!--serve criar exercicios -->
<?php
// Inicia a sessão
session_start();

// Ativa a exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica se o usuário tem permissão (professor ou admin)
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

// Dados da sessão
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

try {
    // Conecta à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca todas as famílias disponíveis para o formulário
    $familiaStmt = $conn->query("SELECT id, nome FROM familias ORDER BY nome");
    $familias = $familiaStmt->fetchAll(PDO::FETCH_ASSOC);

    // Processa o formulário se foi enviado via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recolhe dados do formulário
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $ordem = (int)($_POST['ordem'] ?? 1);
        $id_familia = (int)($_POST['id_familia'] ?? 0);
        $perguntas = $_POST['perguntas'] ?? [];
        $respostas = $_POST['respostas'] ?? [];
        $pontos = $_POST['pontos'] ?? [];
        $videoUrls = $_POST['videos'] ?? [];
        $imagemUrls = $_POST['imagens'] ?? [];
        $criador_id = $_SESSION['id_utilizador'];

        // Valida seleção de família
        if ($id_familia <= 0) {
            die("Erro: É necessário selecionar uma família válida.");
        }

        // Insere exercício principal
        $stmt = $conn->prepare("INSERT INTO exercicios (titulo, descricao, id_familia, criador_id, ordem, data_criacao) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$titulo, $descricao, $id_familia, $criador_id, $ordem]);
        $id_exercicio = $conn->lastInsertId();

        // Insere perguntas
        $pStmt = $conn->prepare("INSERT INTO perguntas (id_exercicio, texto, resposta, pontos) VALUES (?, ?, ?, ?)");
        foreach ($perguntas as $i => $texto) {
            $pStmt->execute([$id_exercicio, $texto, $respostas[$i] ?? '', $pontos[$i] ?? 1]);
        }

        // Insere URLs de vídeos
        $vStmt = $conn->prepare("INSERT INTO videos (id_exercicio, url) VALUES (?, ?)");
        foreach ($videoUrls as $url) {
            if (!empty(trim($url))) {
                $vStmt->execute([$id_exercicio, $url]);
            }
        }

        // Insere URLs de imagens
        $iStmt = $conn->prepare("INSERT INTO imagens (id_exercicio, url) VALUES (?, ?)");
        foreach ($imagemUrls as $url) {
            if (!empty(trim($url))) {
                $iStmt->execute([$id_exercicio, $url]);
            }
        }

        // Redireciona após sucesso
        header("Location: listar_exercicios.php?sucesso=1");
        exit;
    }
} catch (PDOException $e) {
    // Mostra erro de base de dados
    die("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Criar Novo Exercício</title>
  <!-- Importa os estilos CSS -->
  <link rel="stylesheet" href="/projetor/css/formulario.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
</head>
<body>

<!-- Cabeçalho da página -->
<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
    <h1>Instituto de Educação</h1>
  </div>
  <div class="header-centro">
    <h1>Criar Novo Exercício</h1>
  </div>
  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<!-- Layout da página -->
<div class="painel-admin" style="display: flex;">
  <nav>
    <ul>
      <li><a href="/projetor/admin/painel.php">⬅ Voltar ao Painel</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <section class="familia-bloco">
      <h3>Novo Exercício</h3>
      <!-- Formulário de criação de exercício -->
      <form method="post" class="formulario" enctype="multipart/form-data">
        <label for="titulo">Título:</label>
        <input type="text" name="titulo" id="titulo" required>

        <label for="id_familia">Família:</label>
        <select name="id_familia" id="id_familia" required>
          <option value="">-- Escolher família --</option>
          <?php foreach ($familias as $f): ?>
            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="descricao">Descrição:</label>
        <textarea name="descricao" id="descricao" rows="4"></textarea>

        <label for="ordem">Posição (ordem):</label>
        <input type="number" name="ordem" id="ordem" min="1" placeholder="1" required>

        <!-- Campo dinâmico para perguntas -->
        <label>Perguntas:</label>
        <div id="perguntas-container">
          <div class="pergunta-item">
            <input type="text" name="perguntas[]" placeholder="Pergunta" required>
            <textarea name="respostas[]" placeholder="Resposta" rows="11" required></textarea>
            <input type="number" name="pontos[]" placeholder="Pontos" value="1" min="1" required>
          </div>
        </div>
        <br>
        <button type="button" id="btn-adicionar-pergunta">+ Adicionar Pergunta</button>
        <br><br>

        <!-- Vídeos e imagens -->
        <label for="videos">Vídeos (URL local ou do YouTube):</label>
        <input type="text" name="videos[]" id="videos" placeholder="/projetor/videos/categoria/video.mp4 ou https://youtube.com/...">

        <label for="imagens">Imagens (URL de arquivo existente):</label>
        <input type="text" name="imagens[]" id="imagens" placeholder="/projetor/imagens/categoria/imagem.jpg">

        <label for="imagem_upload">Upload de Imagens (1920x1080):</label>
        <input type="file" name="imagem_upload[]" id="imagem_upload" accept="image/*" multiple>

        <!-- Botão de envio -->
        <br><br>
        <button type="submit" class="btn btn-editar">Guardar Exercício</button>
      </form>
    </section>
  </main>
</div>

<!-- Rodapé -->
<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>

<!-- Scripts JS externos -->
<script src="/projetor/js/adicionar_pergunta.js"></script>
<script src="/projetor/js/valida_imagem.js"></script>
</body>
</html>
