<!-- \projetor\aluno\comparar_turma.php-->
 <!--scoreboard turma -->
<?php
session_start();

// Verifica o tipo de utilizador e redireciona se não for aluno
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: /projetor/index.html");
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];
$email = $_SESSION['email'];

try {
    // Conexão com a base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca a turma do utilizador e armazena na sessão, se ainda não existir
    if (!isset($_SESSION['turma'])) {
        $stmt = $conn->prepare("SELECT turma FROM utilizadores WHERE id = ?");
        $stmt->execute([$id_utilizador]);
        $_SESSION['turma'] = $stmt->fetchColumn();
    }

    $turma = $_SESSION['turma'];

    // Consulta para buscar alunos da mesma turma e a sua pontuação total
    $stmt = $conn->prepare("
        SELECT 
            u.nome, 
            u.email,
            COALESCE(p.total_pontos, 0) AS total_pontos
        FROM utilizadores u
        LEFT JOIN (
            SELECT id_utilizador, SUM(melhor_pontos) AS total_pontos
            FROM (
                SELECT id_utilizador, id_exercicio, MAX(pontos) AS melhor_pontos
                FROM classificacao
                GROUP BY id_utilizador, id_exercicio
            ) AS melhor_tentativas
            GROUP BY id_utilizador
        ) AS p ON p.id_utilizador = u.id
        WHERE u.turma = ? AND u.tipo = 'aluno'
        ORDER BY total_pontos DESC, u.nome ASC
    ");
    $stmt->execute([$turma]);
    $colegas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepara os dados para o gráfico de barras (Chart.js)
    $labels = [];
    $pontos = [];
    foreach ($colegas as $c) {
        $labels[] = $c['nome'];
        $pontos[] = $c['total_pontos'];
    }

} catch (PDOException $e) {
    die("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Comparar com a Turma</title>
  <!-- Estilos visuais -->
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/dashboard_aluno.css">
  <link rel="stylesheet" href="/projetor/css/comparar_turma.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <!-- Biblioteca para gráficos -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- Cabeçalho com logo, título e informações do utilizador -->
<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
    <h1>Instituto de Educação</h1>
  </div>

  <div class="header-centro">
    <h1>Ranking Turma</h1>
  </div>

  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<!-- Estrutura com barra lateral e conteúdo principal -->
<div class="dashboard-container">
  <nav class="sidebar">
    <ul>
      <li><a href="/projetor/aluno/dashboard_aluno.php" class="menu-button">⬅ Voltar ao Painel de controlo</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <h2>Classificação da Turma</h2>

    <!-- Gráfico com a pontuação da turma -->
    <h3>Gráfico de Pontuação</h3>
    <canvas id="graficoTurma"></canvas>

    <!-- Tabela com a classificação em formato de lista -->
    <h3>Classificação em Lista</h3>
    <table class="ranking-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th>Email</th>
          <th>Total de Pontos</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($colegas as $i => $c): ?>
          <tr class="<?= $c['email'] === $email ? 'meu-nome' : '' ?>">
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($c['nome']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= $c['total_pontos'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>

<!-- Rodapé -->
<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>

<!-- Script para renderizar o gráfico com Chart.js -->
<script>
  const ctx = document.getElementById('graficoTurma').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Pontos',
        data: <?= json_encode($pontos) ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Pontos'
          }
        }
      },
      plugins: {
        legend: { display: false }
      }
    }
  });
</script>
</body>
</html>
