<?php
session_start();

// Recupera tipo e nome do utilizador a partir da sessão
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

// Verifica permissões de acesso
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

try {
    // Conexão com a base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Coleta dados para filtros
    $alunos = $conn->query("SELECT id, nome FROM utilizadores WHERE tipo = 'aluno' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    $turmas = $conn->query("SELECT DISTINCT turma FROM utilizadores WHERE tipo = 'aluno' AND turma IS NOT NULL ORDER BY turma")->fetchAll(PDO::FETCH_COLUMN);
    $anos = $conn->query("SELECT DISTINCT ano FROM utilizadores WHERE tipo = 'aluno' AND ano IS NOT NULL ORDER BY ano")->fetchAll(PDO::FETCH_COLUMN);
    $instituicoes = $conn->query("SELECT DISTINCT instituicao FROM utilizadores WHERE tipo = 'aluno' AND instituicao IS NOT NULL ORDER BY instituicao")->fetchAll(PDO::FETCH_COLUMN);

    // Filtros recebidos via GET
    $filtroId = $_GET['aluno_id'] ?? '';
    $filtroTurma = $_GET['turma'] ?? '';
    $filtroAno = $_GET['ano'] ?? '';
    $filtroInstituicao = $_GET['instituicao'] ?? '';

    // Consulta principal: acessos com detalhes
    $sql = "
        SELECT 
            u.turma,
            u.ano,
            u.instituicao,
            u.nome,
            u.email,
            e.titulo AS exercicio,
            c.pontos,
            c.data_hora AS ultima_data
        FROM classificacao c
        JOIN utilizadores u ON c.id_utilizador = u.id
        JOIN exercicios e ON c.id_exercicio = e.id
        WHERE u.tipo = 'aluno'
    ";

    $params = [];

    // Aplica filtros se preenchidos
    if (!empty($filtroId)) {
        $sql .= " AND u.id = ?";
        $params[] = $filtroId;
    }
    if (!empty($filtroTurma)) {
        $sql .= " AND u.turma = ?";
        $params[] = $filtroTurma;
    }
    if (!empty($filtroAno)) {
        $sql .= " AND u.ano = ?";
        $params[] = $filtroAno;
    }
    if (!empty($filtroInstituicao)) {
        $sql .= " AND u.instituicao = ?";
        $params[] = $filtroInstituicao;
    }

    // Ordenação
    $sql .= " ORDER BY u.turma, u.nome, e.titulo, c.data_hora DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Registo de Submissões de Exercícios</title>
  <!-- Estilos do sistema -->
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/tabelas.css">
</head>
<body>
  <!-- Cabeçalho -->
  <header class="header-principal">
    <div class="header-esquerda">
      <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
      <h1>Instituto de Educação</h1>
    </div>
    <div class="header-centro">
      <h1>Registo de Submissões de Exercícios</h1>
    </div>
    <div class="user-info">
      <p><strong><?= htmlspecialchars($nome) ?></strong></p>
      <p><em><?= $tipo ?></em></p>
      <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
    </div>
  </header>

  <div class="layout-flex">
    <!-- Menu lateral -->
    <nav>
      <ul>
        <li><a href="/projetor/admin/painel.php">⬅ Voltar ao Painel</a></li>
      </ul>
    </nav>

    <!-- Área principal -->
    <main class="conteudo">
      <h2>Filtros</h2>
      <!-- Formulário de filtros -->
      <form method="get">
        <label>Aluno:</label>
        <select name="aluno_id">
          <option value="">Todos</option>
          <?php foreach ($alunos as $aluno): ?>
            <option value="<?= $aluno['id'] ?>" <?= $aluno['id'] == $filtroId ? 'selected' : '' ?>>
              <?= htmlspecialchars($aluno['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Turma:</label>
        <select name="turma">
          <option value="">Todas</option>
          <?php foreach ($turmas as $turma): ?>
            <option value="<?= $turma ?>" <?= $turma == $filtroTurma ? 'selected' : '' ?>>
              <?= htmlspecialchars($turma) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Ano:</label>
        <select name="ano">
          <option value="">Todos</option>
          <?php foreach ($anos as $ano): ?>
            <option value="<?= $ano ?>" <?= $ano == $filtroAno ? 'selected' : '' ?>>
              <?= htmlspecialchars($ano) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Instituição:</label>
        <select name="instituicao">
          <option value="">Todas</option>
          <?php foreach ($instituicoes as $inst): ?>
            <option value="<?= $inst ?>" <?= $inst == $filtroInstituicao ? 'selected' : '' ?>>
              <?= htmlspecialchars($inst) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button type="submit">Filtrar</button>
      </form>

      <!-- Resultados da pesquisa -->
      <h2>Resultado</h2>
      <?php if (empty($resultados)): ?>
        <p>Nenhum resultado encontrado.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Turma</th>
              <th>Ano</th>
              <th>Instituição</th>
              <th>Nome</th>
              <th>Email</th>
              <th>Exercício</th>
              <th>Pontos</th>
              <th>Data/Hora</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($resultados as $linha): ?>
              <tr>
                <td><?= htmlspecialchars($linha['turma'] ?? '-') ?></td>
                <td><?= htmlspecialchars($linha['ano'] ?? '-') ?></td>
                <td><?= htmlspecialchars($linha['instituicao'] ?? '-') ?></td>
                <td><?= htmlspecialchars($linha['nome']) ?></td>
                <td><?= htmlspecialchars($linha['email']) ?></td>
                <td><?= htmlspecialchars($linha['exercicio']) ?></td>
                <td><?= htmlspecialchars($linha['pontos']) ?></td>
                <td><?= htmlspecialchars($linha['ultima_data']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </main>
  </div>

  <!-- Rodapé -->
  <footer>
    <p>&copy; 2025 - Sistema de Gestão</p>
  </footer>
</body>
</html>
