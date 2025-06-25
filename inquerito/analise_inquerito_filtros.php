 <!-- \projetor\inquerito\anailise_inquerito_filtros.php -->
 <?php
session_start();

// Verifica se o utilizador tem permissão (admin ou professor)
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['admin', 'professor'])) {
    header("Location: /projetor/index.html");
    exit;
}

try {
    // Conexão com a base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recebe os parâmetros de filtro via GET
    $ano = $_GET['ano'] ?? '';
    $turma = $_GET['turma'] ?? '';
    $instituicao = $_GET['instituicao'] ?? '';
    $data_inicio = $_GET['data_inicio'] ?? '';
    $data_fim = $_GET['data_fim'] ?? '';

    // Construção dinâmica da cláusula WHERE
    $where = [];
    $params = [];

    if ($ano !== '') {
        $where[] = 'u.ano = ?';
        $params[] = $ano;
    }
    if ($turma !== '') {
        $where[] = 'u.turma = ?';
        $params[] = $turma;
    }
    if ($instituicao !== '') {
        $where[] = 'u.instituicao = ?';
        $params[] = $instituicao;
    }
    if ($data_inicio !== '') {
        $where[] = 'i.data_resposta >= ?';
        $params[] = $data_inicio;
    }
    if ($data_fim !== '') {
        $where[] = 'i.data_resposta <= ?';
        $params[] = $data_fim . ' 23:59:59';
    }

    // SQL base + filtros se aplicáveis
    $sql = "SELECT i.* FROM inqueritos i JOIN utilizadores u ON i.id_utilizador = u.id";
    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    // Executa a consulta
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $respostas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Campos avaliados no inquérito
    $campos = [
        "rapidez", "bugs", "multidispositivo", "navegacao",
        "conteudo_util", "organizacao", "nivel_dificuldade", "feedback",
        "visual", "facilidade_localizar", "responder_exercicios", "autonomia",
        "desempenho", "recomendacao"
    ];

    // Inicializa totais e frequências por campo
    $totais = [];
    $frequencias = [];
    foreach ($campos as $campo) {
        $totais[$campo] = ["total" => 0, "contagem" => 0];
        $frequencias[$campo] = array_fill(1, 5, 0); // Frequências de 1 a 5
    }

    // Calcula somatórios e contagens
    foreach ($respostas as $resposta) {
        foreach ($campos as $campo) {
            $val = (int)$resposta[$campo];
            if ($val >= 1 && $val <= 5) {
                $totais[$campo]['total'] += $val;
                $totais[$campo]['contagem']++;
                $frequencias[$campo][$val]++;
            }
        }
    }

    // Calcula médias por campo
    foreach ($campos as $campo) {
        $totais[$campo]['media'] = $totais[$campo]['contagem'] > 0
            ? round($totais[$campo]['total'] / $totais[$campo]['contagem'], 2)
            : 0;
    }

    $total_inqueritos = count($respostas);

} catch (PDOException $e) {
    die("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Análise com Filtros</title>

  <!-- Estilos e scripts -->
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/inquerito/analise_inquerito_filtros.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Passagem de dados do PHP para JavaScript -->
  <script>
    const dados = <?= json_encode($totais) ?>;
    const frequencias = <?= json_encode($frequencias) ?>;
  </script>
  <script src="/projetor/js/analise_resultado_filtros.js" defer></script>
</head>

<body>
<!-- Cabeçalho -->
<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" alt="Logo" class="logo-header">
    <h1>Escola Secundária de Exemplo</h1>
  </div>
  <div class="header-centro">
    <h1>Análise com Filtros</h1>
  </div>
  <div class="user-info">
    <p><strong><?= htmlspecialchars($_SESSION['email']) ?></strong></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<!-- Estrutura principal -->
<div class="layout-flex">
  <!-- Navegação e formulário de filtro -->
  <nav class="sidebar">
    <h2>Menu</h2>
    <ul>
      <li><a href="/projetor/admin/painel.php">Dashboard</a></li>
    </ul>

    <h2>Filtros</h2>
    <form method="get">
      <label>Ano:</label>
      <input type="number" name="ano" value="<?= htmlspecialchars($ano) ?>">
      <label>Turma:</label>
      <input type="text" name="turma" value="<?= htmlspecialchars($turma) ?>">
      <label>Instituição:</label>
      <input type="text" name="instituicao" value="<?= htmlspecialchars($instituicao) ?>">
      <label>Data Início:</label>
      <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">
      <label>Data Fim:</label>
      <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>">
      <button type="submit">Filtrar</button>
    </form>
  </nav>

  <!-- Conteúdo principal: análise e gráficos -->
  <main class="conteudo">
    <h2>Total de Inquéritos: <?= $total_inqueritos ?></h2>

    <!-- Tabela com estatísticas de cada campo -->
    <table class="estatisticas">
      <thead>
        <tr>
          <th>Indicador</th>
          <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th>
          <th>Média</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($campos as $campo): ?>
          <tr>
            <td><?= ucwords(str_replace("_", " ", $campo)) ?></td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <td><?= $frequencias[$campo][$i] ?></td>
            <?php endfor; ?>
            <td><?= $totais[$campo]["media"] ?? 0 ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Gráficos individuais de frequência -->
    <div class="estatisticas-container">
      <?php foreach ($campos as $campo): ?>
        <div class="grafico-item">
          <h4><?= ucwords(str_replace("_", " ", $campo)) ?></h4>
          <canvas id="grafico_<?= $campo ?>"></canvas>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Gráfico geral de médias -->
    <h2>Gráfico de Médias</h2>
    <div class="grafico-item">
      <canvas id="grafico_media_geral"></canvas>
    </div>
  </main>
</div>

<!-- Rodapé -->
<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>
</body>
</html>

analise_inquerito.css
/*\projetor\inquerito\analise_inquerito.css*/
/* === ESTILO BASE === */
body {
  background-color: #f4f6f9;
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
  padding: 0;
}

h2, h3 {
  color: #2c3e50;
}

/* === TABELA DE ESTATÍSTICAS === */
table.estatisticas {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
  background-color: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
}

table.estatisticas th,
table.estatisticas td {
  padding: 12px;
  border: 1px solid #ccc;
  text-align: center;
}

table.estatisticas th {
  background-color: #2c3e50;
  color: white;
}

table.estatisticas tr:nth-child(even) {
  background-color: #f9f9f9;
}

/* === CONTAINER DE GRÁFICOS === */
.estatisticas-container {
  display: flex;
  flex-wrap: wrap;
  gap: 2rem;
}

/* === BLOCOS DE GRÁFICO === */
.grafico-item {
  flex: 1 1 calc(50% - 2rem);
  min-width: 300px;
  background-color: #f9fafb;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.grafico-item h4 {
  text-align: center;
  margin-bottom: 1rem;
  color: #1f2937;
}

/* === CANVAS GLOBAL === */
canvas {
  width: 100% !important;
  height: auto !important;
}
