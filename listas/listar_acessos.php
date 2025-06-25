<!--/projetor/listas/listar_acessos.php-->
<?php
session_start();

$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];


if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: /projetor/index.html");
    exit;
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta combinada para obter ações baseadas na criação de dados
    $stmt = $conn->query("
        SELECT nome, email, acao, detalhe, data_hora FROM (
            SELECT u.nome, u.email, 'Criação de Exercício' AS acao, e.titulo AS detalhe, e.data_criacao AS data_hora
            FROM exercicios e
            JOIN utilizadores u ON e.criador_id = u.id

            UNION

            SELECT u.nome, u.email, 'Criação de Família' AS acao, f.nome AS detalhe, f.data_criacao AS data_hora
            FROM familias f
            JOIN utilizadores u ON f.criador_id = u.id

            UNION

            SELECT u.nome, u.email, 'Submissão de Exercício' AS acao, e.titulo AS detalhe, c.data_hora AS data_hora
            FROM classificacao c
            JOIN utilizadores u ON c.id_utilizador = u.id
            JOIN exercicios e ON c.id_exercicio = e.id
        ) AS log_gerado
        ORDER BY data_hora DESC
    ");
    $registos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Relatório de Atividades</title>
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/tabelas.css">
</head>
<body>

<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
    <h1>Instituto de Educação</h1>
  </div>

  <div class="header-centro">
    <h1>Relatório de Atividades dos Utilizadores</h1>
  </div>

  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>


<div class="layout-flex">
  <nav>
    <ul>
      <li><a href="/projetor/admin/painel.php">⬅ Voltar ao Painel</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <h2>Histórico de Ações</h2>
    <?php if (empty($registos)): ?>
      <p>Nenhuma atividade encontrada.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Ação</th>
            <th>Detalhe</th>
            <th>Data/Hora</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registos as $linha): ?>
            <tr>
              <td><?= htmlspecialchars($linha['nome']) ?></td>
              <td><?= htmlspecialchars($linha['email']) ?></td>
              <td><?= htmlspecialchars($linha['acao']) ?></td>
              <td><?= htmlspecialchars($linha['detalhe']) ?></td>
              <td><?= htmlspecialchars($linha['data_hora']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>
</div>

<footer>
  <p>&copy; 2025 - Sistema de Gestão</p>
</footer>
</body>
</html>
