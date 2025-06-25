<!-- /projetor/listas/logins_recentes.php  -->
<?php
session_start();

if (!isset($_SESSION['logado']) || !in_array($_SESSION['tipo'], ['admin', 'professor'])) {
    die("Acesso negado.");
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=pythonr;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao ligar à base de dados: " . $e->getMessage());
}

$tipo_filtro = $_GET['tipo'] ?? '';
$nome_filtro = $_GET['nome'] ?? '';
$email_filtro = $_GET['email'] ?? '';
$instituicao_filtro = $_GET['instituicao'] ?? '';
$ano_filtro = $_GET['ano'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Buscar opções únicas para nome, email, instituição e ano
$opcoes = $conn->query("SELECT DISTINCT nome, email, instituicao, ano FROM utilizadores")->fetchAll(PDO::FETCH_ASSOC);
$nomes = array_unique(array_column($opcoes, 'nome'));
$emails = array_unique(array_column($opcoes, 'email'));
$instituicoes = array_unique(array_column($opcoes, 'instituicao'));
$anos = array_unique(array_column($opcoes, 'ano'));

$query = "
    SELECT l.data_hora, l.ip_address, u.nome, u.email, u.tipo, u.instituicao, u.ano
    FROM login_log l
    JOIN utilizadores u ON l.id_utilizador = u.id
    WHERE 1 = 1
";

$params = [];
if ($tipo_filtro && in_array($tipo_filtro, ['aluno', 'professor', 'admin'])) {
    $query .= " AND u.tipo = ?";
    $params[] = $tipo_filtro;
}
if ($nome_filtro) {
    $query .= " AND LOWER(u.nome) LIKE ?";
    $params[] = '%' . strtolower($nome_filtro) . '%';
}
if ($email_filtro) {
    $query .= " AND LOWER(u.email) LIKE ?";
    $params[] = '%' . strtolower($email_filtro) . '%';
}
if ($instituicao_filtro) {
    $query .= " AND LOWER(u.instituicao) LIKE ?";
    $params[] = '%' . strtolower($instituicao_filtro) . '%';
}
if ($ano_filtro) {
    $query .= " AND u.ano = ?";
    $params[] = $ano_filtro;
}
if ($data_inicio) {
    $query .= " AND l.data_hora >= ?";
    $params[] = $data_inicio . " 00:00:00";
}
if ($data_fim) {
    $query .= " AND l.data_hora <= ?";
    $params[] = $data_fim . " 23:59:59";
}

$query .= " ORDER BY l.data_hora DESC LIMIT 100";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$registros = $stmt->fetchAll();

$nome = $_SESSION['email'];
$tipo = $_SESSION['tipo'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Logins Recentes</title>
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/listar_familias.css">
  <link rel="stylesheet" href="/projetor/css/tabelas.css">
</head>
<body>
<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
    <h1>Instituto de Educação</h1>
  </div>
  <div class="header-centro">
    <h1>Logins Recentes</h1>
  </div>
  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= htmlspecialchars($tipo) ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<div style="display: flex;">
  <nav>
    <h2>Menu</h2>
    <ul>
      <li><a href="/projetor/admin/painel.php">⬅ Voltar ao Painel</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <br>
    <form method="GET" class="filtros">
      <label for="tipo">Tipo:</label>
      <select name="tipo" id="tipo">
        <option value="">Todos</option>
        <option value="aluno" <?= $tipo_filtro === 'aluno' ? 'selected' : '' ?>>Aluno</option>
        <option value="professor" <?= $tipo_filtro === 'professor' ? 'selected' : '' ?>>Professor</option>
        <option value="admin" <?= $tipo_filtro === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>

      <label for="nome">Nome:</label>
      <select name="nome" id="nome">
        <option value="">Todos</option>
        <?php foreach ($nomes as $n): ?>
          <option value="<?= htmlspecialchars($n) ?>" <?= $nome_filtro === $n ? 'selected' : '' ?>><?= htmlspecialchars($n) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="email">Email:</label>
      <select name="email" id="email">
        <option value="">Todos</option>
        <?php foreach ($emails as $e): ?>
          <option value="<?= htmlspecialchars($e) ?>" <?= $email_filtro === $e ? 'selected' : '' ?>><?= htmlspecialchars($e) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="instituicao">Instituição:</label>
      <select name="instituicao" id="instituicao">
        <option value="">Todas</option>
        <?php foreach ($instituicoes as $i): ?>
          <option value="<?= htmlspecialchars($i) ?>" <?= $instituicao_filtro === $i ? 'selected' : '' ?>><?= htmlspecialchars($i) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="ano">Ano:</label>
      <select name="ano" id="ano">
        <option value="">Todos</option>
        <?php foreach ($anos as $a): ?>
          <option value="<?= htmlspecialchars($a) ?>" <?= $ano_filtro === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="data_inicio">Início:</label>
      <input type="date" name="data_inicio" id="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">

      <label for="data_fim">Fim:</label>
      <input type="date" name="data_fim" id="data_fim" value="<?= htmlspecialchars($data_fim) ?>">

      <button type="submit">Filtrar</button>
    </form>

    <div class="tabela-container">
      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Instituição</th>
            <th>Ano</th>
            <th>Data/Hora</th>
            <th>IP</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registros as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['nome']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['tipo']) ?></td>
              <td><?= htmlspecialchars($row['instituicao']) ?></td>
              <td><?= htmlspecialchars($row['ano']) ?></td>
              <td><?= htmlspecialchars($row['data_hora']) ?></td>
              <td><?= htmlspecialchars($row['ip_address']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (count($registros) === 0): ?>
            <tr><td colspan="7">Nenhum registro encontrado.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>
</body>
</html>
