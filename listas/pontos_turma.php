 <!-- \projetor\listas\pontos_turma.php -->
 <?php
session_start();

// Garante que apenas administradores ou professores podem aceder
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

try {
    // Conecta à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obter lista de turmas distintas
    $turmas = $conn->query("SELECT DISTINCT turma FROM utilizadores WHERE tipo = 'aluno' AND turma IS NOT NULL ORDER BY turma")->fetchAll(PDO::FETCH_COLUMN);

    // Turma selecionada via GET
    $turmaSelecionada = $_GET['turma'] ?? null;
    $dados = [];

    if ($turmaSelecionada) {
        $stmt = $conn->prepare("
            SELECT u.nome, u.email, u.turma, SUM(c.pontos) AS total_pontos
            FROM utilizadores u
            LEFT JOIN classificacao c ON u.id = c.id_utilizador
            WHERE u.tipo = 'aluno' AND u.turma = ?
            GROUP BY u.id
            ORDER BY u.nome
        ");
        $stmt->execute([$turmaSelecionada]);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Pontuação por Turma</title>
  <!-- Estilos do sistema -->
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/tabelas.css">
</head>
<body>
  <!-- Cabeçalho com dados do utilizador -->
  <header class="header-principal">
    <div class="header-esquerda">
      <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
      <h1>Instituto de Educação</h1>
    </div>
    <div class="header-centro">
      <h1>Pontuação por Turma</h1>
    </div>
    <div class="user-info">
      <p><strong><?= htmlspecialchars($nome) ?></strong></p>
      <p><em><?= htmlspecialchars($tipo) ?></em></p>
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

    <!-- Conteúdo principal -->
    <main class="conteudo">
      <!-- Formulário de seleção de turma -->
      <form method="get">
        <label for="turma">Selecionar turma:</label>
        <select name="turma" id="turma" required>
          <option value="">-- Escolha uma turma --</option>
          <?php foreach ($turmas as $turma): ?>
            <option value="<?= htmlspecialchars($turma) ?>" <?= $turma === $turmaSelecionada ? 'selected' : '' ?>>
              <?= htmlspecialchars($turma) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Ver Pontuações</button>
      </form>

      <?php if ($turmaSelecionada): ?>
        <h2>Alunos da turma: <?= htmlspecialchars($turmaSelecionada) ?></h2>

        <?php if (count($dados) > 0): ?>
          <!-- Tabela com resultados da turma -->
          <table>
            <thead>
              <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Pontuação Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dados as $linha): ?>
                <tr>
                  <td><?= htmlspecialchars($linha['nome']) ?></td>
                  <td><?= htmlspecialchars($linha['email']) ?></td>
                  <td><?= $linha['total_pontos'] ?? 0 ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>Nenhum aluno com pontuação encontrada para esta turma.</p>
        <?php endif; ?>
      <?php endif; ?>
    </main>
  </div>

  <!-- Rodapé -->
  <footer>
    <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
  </footer>
</body>
</html>

