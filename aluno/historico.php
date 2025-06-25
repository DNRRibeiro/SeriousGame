<!-- \projetor\aluno\historico.php-->
 <!-- consulta de historico exercicios-->
<?php
session_start();

$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: /projetor/index.html");
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];
$email = $_SESSION['email'];

try {
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar famílias
    $familias = $conn->query("SELECT id, nome FROM familias ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Buscar exercícios
    $exercicios = $conn->query("SELECT id, titulo FROM exercicios ORDER BY titulo ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Filtros
    $condicoes = ["c.id_utilizador = :id_utilizador"];
    $params = ['id_utilizador' => $id_utilizador];

    if (!empty($_GET['familia'])) {
        $condicoes[] = "e.id_familia = :familia";
        $params['familia'] = $_GET['familia'];
    }

    if (!empty($_GET['exercicio'])) {
        $condicoes[] = "e.id = :exercicio";
        $params['exercicio'] = $_GET['exercicio'];
    }

    if (!empty($_GET['data_inicio'])) {
        $condicoes[] = "c.data_hora >= :data_inicio";
        $params['data_inicio'] = $_GET['data_inicio'] . " 00:00:00";
    }

    if (!empty($_GET['data_fim'])) {
        $condicoes[] = "c.data_hora <= :data_fim";
        $params['data_fim'] = $_GET['data_fim'] . " 23:59:59";
    }

    $where = implode(" AND ", $condicoes);

    // Consulta com filtros
    $stmt = $conn->prepare("
        SELECT f.nome AS familia, e.titulo AS exercicio, c.pontos, c.data_hora
        FROM classificacao c
        JOIN exercicios e ON c.id_exercicio = e.id
        JOIN familias f ON e.id_familia = f.id
        WHERE $where
        ORDER BY c.data_hora DESC
    ");
    $stmt->execute($params);
    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total de pontos (com os mesmos filtros)
    $stmtTotal = $conn->prepare("
        SELECT SUM(c.pontos) AS total
        FROM classificacao c
        JOIN exercicios e ON c.id_exercicio = e.id
        WHERE $where
    ");
    $stmtTotal->execute($params);
    $total_pontos = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Histórico de Pontos</title>
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/tabelas.css">

  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/conteudo.css">
  
</head>
<body>

<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
    <h1>Instituto de Educação</h1>
  </div>

  <div class="header-centro">
    <h1>Histórico Atividade</h1>
  </div>

  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>


<div class="layout-flex">
  <nav class="sidebar">
    <ul>
      <li><a href="/projetor/aluno/dashboard_aluno.php">⬅ Painel de Controlo</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <h2>Total de Pontos: <?= htmlspecialchars($total_pontos) ?></h2>

    <form method="GET" class="filtros" style="margin-bottom: 20px;">
      <label>Família:
        <select name="familia">
          <option value="">Todas</option>
          <?php foreach ($familias as $f): ?>
            <option value="<?= $f['id'] ?>" <?= (isset($_GET['familia']) && $_GET['familia'] == $f['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($f['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Exercício:
        <select name="exercicio">
          <option value="">Todos</option>
          <?php foreach ($exercicios as $ex): ?>
            <option value="<?= $ex['id'] ?>" <?= (isset($_GET['exercicio']) && $_GET['exercicio'] == $ex['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($ex['titulo']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Data Início:
        <input type="date" name="data_inicio" value="<?= $_GET['data_inicio'] ?? '' ?>">
      </label>

      <label>Data Fim:
        <input type="date" name="data_fim" value="<?= $_GET['data_fim'] ?? '' ?>">
      </label>

      <button type="submit">Filtrar</button>
      <a href="historico.php"><button type="button">Mostrar Todos</button></a>
    </form>

    <?php if (empty($historico)): ?>
      <p>Sem registos encontrados.</p>
    <?php else: ?>
      <table class="tabela-pontos">
        <thead>
          <tr>
            <th>Família</th>
            <th>Exercício</th>
            <th>Pontos</th>
            <th>Data/Hora</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($historico as $linha): ?>
            <tr>
              <td><?= htmlspecialchars($linha['familia']) ?></td>
              <td><?= htmlspecialchars($linha['exercicio']) ?></td>
              <td><?= htmlspecialchars($linha['pontos']) ?></td>
              <td><?= htmlspecialchars($linha['data_hora']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>
</div>

<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>
</body>
</html>
