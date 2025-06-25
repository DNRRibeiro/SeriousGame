<?php
session_start();

if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['admin', 'professor'])) {
    header("Location: /projetor/index.html");
    exit;
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SELECT * FROM inqueritos");
    $respostas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $campos = [
        "rapidez", "bugs", "multidispositivo", "navegacao",
        "conteudo_util", "organizacao", "nivel_dificuldade", "feedback",
        "visual", "facilidade_localizar", "responder_exercicios", "autonomia",
        "desempenho", "recomendacao"
    ];

    $totais = [];
    $frequencias = [];
    foreach ($campos as $campo) {
        $totais[$campo] = ["total" => 0, "contagem" => 0];
        $frequencias[$campo] = array_fill(1, 5, 0);
    }

    foreach ($respostas as $resposta) {
        foreach ($campos as $campo) {
            $val = (int)$resposta[$campo];
            $totais[$campo]["total"] += $val;
            $totais[$campo]["contagem"]++;
            if (isset($frequencias[$campo][$val])) {
                $frequencias[$campo][$val]++;
            }
        }
    }

    foreach ($totais as $campo => &$dados) {
        $dados["media"] = $dados["contagem"] > 0
            ? round($dados["total"] / $dados["contagem"], 2)
            : 0;
    }

    $total_inqueritos = count($respostas);

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Análise de Inquérito</title>
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/tabelas.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const totais = <?= json_encode($totais) ?>;
    const frequencias = <?= json_encode($frequencias) ?>;
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      Object.keys(frequencias).forEach(campo => {
        const ctx = document.getElementById("grafico_" + campo)?.getContext("2d");
        if (ctx) {
          new Chart(ctx, {
            type: 'bar',
            data: {
              labels: ["1", "2", "3", "4", "5"],
              datasets: [{
                label: campo.replace("_", " ").toUpperCase(),
                data: Object.values(frequencias[campo]),
                backgroundColor: '#3498db'
              }]
            },
            options: {
              responsive: true,
              scales: {
                y: {
                  beginAtZero: true,
                  stepSize: 1
                }
              }
            }
          });
        }
      });

      const ctxMedia = document.getElementById("grafico_media_geral").getContext("2d");
      new Chart(ctxMedia, {
        type: 'bar',
        data: {
          labels: Object.keys(totais).map(c => c.replace("_", " ")),
          datasets: [{
            label: "Média",
            data: Object.values(totais).map(c => c.media),
            backgroundColor: '#2ecc71'
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              max: 5
            }
          }
        }
      });
    });
  </script>
</head>
<body>
<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" alt="Logo" class="logo-header">
    <h1>Instituto de Educação</h1>
  </div>
  <div class="header-centro">
    <h1>Análise de Inquérito</h1>
  </div>
  <div class="user-info">
    <p><strong><?= htmlspecialchars($_SESSION['email']) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<div class="layout-flex">
  <!-- NAV LATERAL -->
  <nav class="sidebar">
    <h2>Menu</h2>
    <ul>
      <li><a href="/projetor/admin/painel.php">Dashboard</a></li>
      <li><a href="/projetor/inquerito/analise_inquerito.php">Análise Global</a></li>
      <li><a href="/projetor/inquerito/analise_inquerito_filtros.php">Análise com Filtros</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <h2>Total de Inquéritos Preenchidos: <?= $total_inqueritos ?></h2>

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

    <h2>Gráficos Individuais</h2>
    <div class="estatisticas-container">
      <?php foreach ($campos as $campo): ?>
        <div class="grafico-item">
          <h4><?= ucwords(str_replace("_", " ", $campo)) ?></h4>
          <canvas id="grafico_<?= $campo ?>"></canvas>
        </div>
      <?php endforeach; ?>
    </div>

    <h2>Gráfico de Médias</h2>
    <div class="grafico-item">
      <canvas id="grafico_media_geral"></canvas>
    </div>
  </main>
</div>

<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>
</body>
</html>
