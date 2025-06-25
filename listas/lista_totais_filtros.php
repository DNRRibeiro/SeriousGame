 <!-- \projetor\listas\lista_totais_filtros.php -->
 <?php
session_start();

// Verifica se o utilizador é professor ou admin
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

// Armazena informações da sessão
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

try {
    // Conecta à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar listas para filtros
    $alunos = $conn->query("SELECT id, nome FROM utilizadores WHERE tipo = 'aluno' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    $turmas = $conn->query("SELECT DISTINCT turma FROM utilizadores WHERE tipo = 'aluno' AND turma IS NOT NULL ORDER BY turma")->fetchAll(PDO::FETCH_COLUMN);
    $anos = $conn->query("SELECT DISTINCT ano FROM utilizadores WHERE tipo = 'aluno' AND ano IS NOT NULL ORDER BY ano")->fetchAll(PDO::FETCH_COLUMN);
    $instituicoes = $conn->query("SELECT DISTINCT instituicao FROM utilizadores WHERE tipo = 'aluno' AND instituicao IS NOT NULL ORDER BY instituicao")->fetchAll(PDO::FETCH_COLUMN);

    // Captura filtros enviados por GET
    $filtroId = $_GET['aluno_id'] ?? '';
    $filtroTurma = $_GET['turma'] ?? '';
    $filtroAno = $_GET['ano'] ?? '';
    $filtroInstituicao = $_GET['instituicao'] ?? '';

    // Query principal para somar as melhores pontuações por aluno
    $sql = "
        SELECT 
            u.id,
            u.nome,
            u.email,
            u.turma,
            u.ano,
            u.instituicao,
            SUM(melhor.pontos) AS total_pontos
        FROM utilizadores u
        JOIN (
            SELECT id_utilizador, id_exercicio, MAX(pontos) AS pontos
            FROM classificacao
            GROUP BY id_utilizador, id_exercicio
        ) AS melhor ON melhor.id_utilizador = u.id
        WHERE u.tipo = 'aluno'
    ";

    $params = [];

    // Aplica filtros dinamicamente
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

    // Agrupa e ordena os resultados
    $sql .= " GROUP BY u.id, u.nome, u.email, u.turma, u.ano, u.instituicao ORDER BY u.turma, u.nome";
    
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
  <title>Resumo de Pontos por Aluno (Melhores Tentativas)</title>
  <!-- Importa os estilos do projeto -->
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/tabelas.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
</head>
<body>
  <!-- Cabeçalho principal com logo e info do utilizador -->
  <header class="header-principal">
    <div class="header-esquerda">
      <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
      <h1>Instituto de Educação</h1>
    </div>
    <div class="header-centro">
      <h1>Resumo de Pontos</h1>
    </div>
    <div class="user-info">
      <p><strong><?= htmlspecialchars($nome) ?></strong></p>
      <p><em><?= $tipo ?></em></p>
      <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
    </div>
  </header>

  <!-- Layout principal com menu lateral e conteúdo -->
  <div class="layout-flex">
    <!-- Menu de navegação lateral -->
    <nav>
      <ul>
        <li><a href="/projetor/admin/painel.php">⬅ Voltar ao Painel</a></li>
      </ul>
    </nav>

    <main class="conteudo">
      <h2>Filtros</h2>
      <!-- Formulário com filtros dinâmicos -->
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

      <!-- Tabela de resultados com pontuação total por aluno -->
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
              <th>Total de Pontos</th>
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
                <td><?= htmlspecialchars($linha['total_pontos']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </main>
  </div>

  <!-- Rodapé do sistema -->
  <footer>
    <p>&copy; 2025 - Sistema de Gestão</p>
  </footer>
</body>
</html>

