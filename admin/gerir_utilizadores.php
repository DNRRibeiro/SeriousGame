<!---\projetor\admin\gerir_utiizadores.php--->
<!--serve consultar utizadores criados -->
<?php
// Início da sessão para aceder às variáveis de sessão
session_start();

// Verifica se o utilizador tem permissões de administrador
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: /projetor/index.html");
    exit;
}

// Guarda o tipo e o email do utilizador logado
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

try {
    // Ligação à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtenção dos filtros opcionais submetidos por GET
    $filtro_tipo = $_GET['tipo'] ?? '';
    $filtro_turma = $_GET['turma'] ?? '';
    $filtro_instituicao = $_GET['instituicao'] ?? '';
    $filtro_ano = $_GET['ano'] ?? '';

    // Construção da query SQL base
    $sql = "SELECT id, nome, email, tipo, turma, ano, instituicao FROM utilizadores WHERE 1";
    $params = [];

    // Aplicação dos filtros, se fornecidos
    if (!empty($filtro_tipo)) {
        $sql .= " AND tipo = ?";
        $params[] = $filtro_tipo;
    }

    if (!empty($filtro_turma)) {
        $sql .= " AND turma = ?";
        $params[] = $filtro_turma;
    }

    if (!empty($filtro_instituicao)) {
        $sql .= " AND instituicao = ?";
        $params[] = $filtro_instituicao;
    }

    if (!empty($filtro_ano)) {
        $sql .= " AND ano = ?";
        $params[] = $filtro_ano;
    }

    $sql .= " ORDER BY tipo, nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $utilizadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtenção dos valores distintos para os filtros
    $turmas = $conn->query("SELECT DISTINCT turma FROM utilizadores WHERE turma IS NOT NULL ORDER BY turma")->fetchAll(PDO::FETCH_COLUMN);
    $instituicoes = $conn->query("SELECT DISTINCT instituicao FROM utilizadores WHERE instituicao IS NOT NULL ORDER BY instituicao")->fetchAll(PDO::FETCH_COLUMN);
    $anos = $conn->query("SELECT DISTINCT ano FROM utilizadores WHERE ano IS NOT NULL ORDER BY ano")->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Gerir Utilizadores</title>
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/tabelas.css">
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
    <h1>Gerir Utilizadores</h1>
  </div>
  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<div class="painel-admin">
  <nav>
    <ul>
      <li><a href="/projetor/admin/painel.php">⬅ Voltar ao Painel</a></li>
      <li><a href="/projetor/admin/criar_utilizador.php">Criar Utilizador</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <h2>Filtros</h2>
    <!-- Formulário de filtros para pesquisa -->
    <form method="get" class="filtros">
      <label for="tipo">Tipo:</label>
      <select name="tipo" id="tipo">
        <option value="">Todos</option>
        <option value="aluno" <?= $filtro_tipo === 'aluno' ? 'selected' : '' ?>>Aluno</option>
        <option value="professor" <?= $filtro_tipo === 'professor' ? 'selected' : '' ?>>Professor</option>
        <option value="admin" <?= $filtro_tipo === 'admin' ? 'selected' : '' ?>>Administrador</option>
      </select>

      <label for="turma">Turma:</label>
      <select name="turma" id="turma">
        <option value="">Todas</option>
        <?php foreach ($turmas as $t): ?>
          <option value="<?= $t ?>" <?= $t === $filtro_turma ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>

      <label for="instituicao">Instituição:</label>
      <select name="instituicao" id="instituicao">
        <option value="">Todas</option>
        <?php foreach ($instituicoes as $inst): ?>
          <option value="<?= $inst ?>" <?= $inst === $filtro_instituicao ? 'selected' : '' ?>><?= htmlspecialchars($inst) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="ano">Ano:</label>
      <select name="ano" id="ano">
        <option value="">Todos</option>
        <?php foreach ($anos as $ano): ?>
          <option value="<?= $ano ?>" <?= $ano == $filtro_ano ? 'selected' : '' ?>><?= htmlspecialchars($ano) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Filtrar</button>
    </form>

    <!-- Tabela de resultados -->
    <?php if (count($utilizadores) === 0): ?>
      <p>Nenhum utilizador encontrado com os critérios selecionados.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Turma</th>
            <th>Ano</th>
            <th>Instituição</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($utilizadores as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['nome']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['tipo']) ?></td>
              <td><?= htmlspecialchars($u['turma']) ?></td>
              <td><?= htmlspecialchars($u['ano']) ?></td>
              <td><?= htmlspecialchars($u['instituicao']) ?></td>
              <td>
                <a href="editar_utilizador.php?id=<?= $u['id'] ?>">
                  <button class="edit">✏️</button>
                </a>
                <a href="remover_utilizador.php?id=<?= $u['id'] ?>" onclick="return confirm('Deseja apagar este utilizador?');">
                  <button class="danger">🗑️</button>
                </a>
              </td>
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
