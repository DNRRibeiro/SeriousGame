<?php
// Início da sessão
session_start();

// Verifica se o utilizador é um aluno autenticado
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: /projetor/index.html");
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];

try {
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
        SELECT SUM(max_pontos) AS total_pontos
        FROM (
            SELECT MAX(pontos) AS max_pontos
            FROM classificacao
            WHERE id_utilizador = ?
            GROUP BY id_exercicio
        ) AS sub
    ");
    $stmt->execute([$id_utilizador]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_pontos = $resultado['total_pontos'] ?? 0;

    $stmt_max = $conn->query("SELECT SUM(pontos) as max_pontos FROM perguntas");
    $resultado_max = $stmt_max->fetch(PDO::FETCH_ASSOC);
    $max_pontos = $resultado_max['max_pontos'] ?? 1;

    $percentagem = $max_pontos > 0 ? round(($total_pontos / $max_pontos) * 100) : 0;

    $stmt = $conn->prepare("
        SELECT c.id_exercicio, MAX(c.pontos) as pontos
        FROM classificacao c
        WHERE c.id_utilizador = ?
        GROUP BY c.id_exercicio
    ");
    $stmt->execute([$id_utilizador]);
    $registos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $acessos = [];
    foreach ($registos as $reg) {
        $stmtInfo = $conn->prepare("
            SELECT e.titulo, f.nome AS familia_nome
            FROM exercicios e
            JOIN familias f ON e.id_familia = f.id
            WHERE e.id = ?
        ");
        $stmtInfo->execute([$reg['id_exercicio']]);
        $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if ($info) {
            $acessos[] = [
                'familia_nome' => $info['familia_nome'],
                'titulo' => $info['titulo'],
                'pontos' => $reg['pontos']
            ];
        }
    }

    $stmt3 = $conn->query("SELECT id, nome FROM familias ORDER BY ordem ASC");
    $familias = $stmt3->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Dashboard do Aluno</title>
  <link rel="stylesheet" href="/projetor/css/dash_aluno.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
</head>
<body>
  <div class="pagina-flex">
    <header class="header-principal">
      <div class="header-esquerda">
        <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
        <h1>Instituto de Educação</h1>
      </div>
      <div class="header-centro">
        <h1>Painel de Controlo - Aluno</h1>
      </div>
      <div class="user-info">
        <p><strong><?= htmlspecialchars($nome) ?></strong></p>
        <p><em><?= $tipo ?></em></p>
        <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
      </div>
    </header>

    <div class="dashboard-container">
      <nav class="sidebar">
        <h2>Menu</h2>
        <ul>
          <li><a href="/projetor/aluno/historico.php">Histórico</a></li>
          <li><a href="/projetor/aluno/listar_todos_exercicios.php">Todos os Exercícios</a></li>
          <li><a href="/projetor/aluno/comparar_turma.php">Ranking</a></li>
        </ul>

        <h2>Exercícios</h2>
        <ul>
          <?php foreach ($familias as $familia): ?>
            <li><strong><?= htmlspecialchars($familia['nome']) ?></strong></li>
            <?php
              $stmt4 = $conn->prepare("SELECT id, titulo FROM exercicios WHERE id_familia = ? ORDER BY ordem ASC");
              $stmt4->execute([$familia['id']]);
              $exercicios = $stmt4->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <ul>
              <?php foreach ($exercicios as $ex): ?>
                <?php
                  $stmtFam = $conn->prepare("SELECT f.nome FROM familias f JOIN exercicios e ON f.id = e.id_familia WHERE e.id = ?");
                  $stmtFam->execute([$ex['id']]);
                  $fam = $stmtFam->fetch(PDO::FETCH_ASSOC);
                  $familia_nome = strtolower($fam['nome'] ?? '');
                  $destino = ($familia_nome === 'code' || $familia_nome === 'code-jogos')
                      ? '/projetor/aluno/executar_python.php'
                      : '/projetor/aluno/responder_exercicio.php';
                ?>
                <li>
                  <a href="<?= $destino ?>?id=<?= $ex['id'] ?>">
                    <?= htmlspecialchars($ex['titulo']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endforeach; ?>
        </ul>

        <h2>Inquérito</h2>
        <ul>
          <li><a href="/projetor/inquerito/inquerito.php">Inquérito</a></li>
        </ul>
      </nav>

      <main class="conteudo">
        <h2>Resumo do Progresso</h2>
        <div id="progresso-container">
          <p><strong>Progresso:</strong> <?= $percentagem ?>% (<?= $total_pontos ?> de <?= $max_pontos ?> pontos)</p>
          <progress id="barra-progresso" max="<?= $max_pontos ?>" value="<?= $total_pontos ?>"></progress>
        </div>

        <h3>Histórico de Exercícios Resolvidos</h3>
        <table class="tabela-pontos">
          <thead>
            <tr>
              <th>Família</th>
              <th>Exercício</th>
              <th>Pontos</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($acessos) === 0): ?>
              <tr><td colspan="3">Ainda não realizou nenhum exercício.</td></tr>
            <?php else: ?>
              <?php foreach ($acessos as $linha): ?>
                <tr>
                  <td><?= htmlspecialchars($linha['familia_nome']) ?></td>
                  <td><?= htmlspecialchars($linha['titulo']) ?></td>
                  <td><?= $linha['pontos'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </main>
    </div>

    <footer>
      <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
    </footer>
  </div>
</body>
</html>
