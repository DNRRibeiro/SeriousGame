<?php
session_start();

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: /projetor/index.html");
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];
$nome_utilizador = $_SESSION['email'];

try {
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Melhor tentativa do aluno por exercício
    $stmt = $conn->prepare("
        SELECT id_exercicio, MAX(pontos) AS pontos
        FROM classificacao
        WHERE id_utilizador = ?
        GROUP BY id_exercicio
    ");
    $stmt->execute([$id_utilizador]);
    $resolvidos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Lista de famílias
    $familias = $conn->query("SELECT id, nome FROM familias ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Todos os Exercícios</title>
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/listar_todos_exercicios.css">
</head>
<body>
<div class="pagina-flex">
  <header class="header-principal">
    <div class="header-esquerda">
      <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
      <h1>Instituto de Educação</h1>
    </div>
    <div class="header-centro">
      <h1>Todos os Exercícios</h1>
    </div>
    <div class="user-info">
      <p><strong><?= htmlspecialchars($nome_utilizador) ?></strong></p>
      <p><em>Aluno</em></p>
      <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
    </div>
  </header>

  <div class="painel-admin">
    <nav class="sidebar">
      <ul>
        <li><a href="/projetor/aluno/dashboard_aluno.php" class="menu-button">⬅ Voltar ao Painel de controlo</a></li>
      </ul>
    </nav>

    <main class="conteudo">
      <?php foreach ($familias as $familia): ?>
        <section class="familia-exercicios">
          <h2><?= htmlspecialchars($familia['nome']) ?></h2>
          <div class="exercicios-lista">
            <?php
              $stmt = $conn->prepare("
                SELECT e.id, e.titulo, 
                       COALESCE(SUM(p.pontos), 0) AS total_pontos, 
                       f.nome AS familia_nome
                FROM exercicios e
                LEFT JOIN perguntas p ON e.id = p.id_exercicio
                JOIN familias f ON f.id = e.id_familia
                WHERE e.id_familia = ?
                GROUP BY e.id, e.titulo, f.nome
                ORDER BY e.ordem ASC
              ");
              $stmt->execute([$familia['id']]);
              $exercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php foreach ($exercicios as $ex): ?>
              <?php
                $feito = array_key_exists($ex['id'], $resolvidos);
                $classe = $feito ? 'botao-feito' : 'botao-pendente';
                $pontos_obtidos = $feito ? $resolvidos[$ex['id']] : 0;
                $total = $ex['total_pontos'];
                $familia_nome = strtolower($ex['familia_nome']);
                $destino = ($familia_nome === 'code' || $familia_nome === 'code-jogos') ? '/projetor/aluno/executar_python.php' : '/projetor/aluno/responder_exercicio.php';
              ?>
              <a href="<?= $destino ?>?id=<?= $ex['id'] ?>">
                <button class="<?= $classe ?>">
                  <?= htmlspecialchars($ex['titulo']) ?><br>
                  Pontos: <?= $pontos_obtidos ?> / <?= $total ?>
                </button>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>
    </main>
  </div>

  <footer>
    <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
  </footer>
</div>
</body>
</html>
