 <!-- \projetor\listas\logins_aluno.php -->
 <?php
session_start();

// Verifica se o utilizador tem permissão (admin ou professor)
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['admin', 'professor'])) {
    header("Location: /projetor/index.html");
    exit;
}

try {
    // Conecta à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca lista de alunos para seleção
    $alunos = $conn->query("SELECT id, nome FROM utilizadores WHERE tipo = 'aluno' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

    $aluno_id = $_GET['aluno_id'] ?? null;
    $dados = [];

    // Se um aluno for selecionado, busca os acessos dele
    if ($aluno_id) {
        $stmt = $conn->prepare("
            SELECT c.data_hora, c.pagina, c.pontos
            FROM classificacao c
            WHERE c.id_utilizador = ?
            ORDER BY c.data_hora DESC
        ");
        $stmt->execute([$aluno_id]);
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
  <title>Logins do Aluno</title>
  <!-- Estilos do sistema -->
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/tabelas.css">
</head>
<body>
  <!-- Cabeçalho com identidade do sistema -->
  <header>
    <h1>Histórico de Acessos do Aluno</h1>
    <p>Utilizador: <strong><?= htmlspecialchars($_SESSION['email']) ?></strong></p>
  </header>

  <div class="layout-flex">
    <!-- Menu lateral de navegação -->
    <nav>
      <ul>
        <li><a href="/projetor/admin/painel.php">⬅ Voltar ao Painel</a></li>
      </ul>
    </nav>

    <!-- Conteúdo principal -->
    <main class="conteudo">
      <!-- Formulário de seleção de aluno -->
      <form method="get">
        <label for="aluno_id">Selecionar aluno:</label>
        <select name="aluno_id" id="aluno_id" required>
          <option value="">-- Escolha um aluno --</option>
          <?php foreach ($alunos as $aluno): ?>
            <option value="<?= $aluno['id'] ?>" <?= $aluno['id'] == $aluno_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($aluno['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Ver acessos</button>
      </form>

      <!-- Se um aluno foi selecionado, mostra os dados -->
      <?php if ($aluno_id): ?>
        <h2>Acessos registados</h2>

        <?php if (count($dados) > 0): ?>
          <table>
            <thead>
              <tr>
                <th>Data/Hora</th>
                <th>Página</th>
                <th>Pontos</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dados as $linha): ?>
                <tr>
                  <td><?= htmlspecialchars($linha['data_hora']) ?></td>
                  <td><?= htmlspecialchars($linha['pagina']) ?></td>
                  <td><?= $linha['pontos'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>Este aluno ainda não tem acessos registados.</p>
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
