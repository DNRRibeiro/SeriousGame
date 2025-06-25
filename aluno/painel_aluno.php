<!---\aluno\painel_aluno.php--->

<?php
// Início da sessão
session_start();

// Garante que apenas utilizadores do tipo 'aluno' possam aceder
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: /projetor/index.html");
    exit;
}

// Recupera o nome/email do utilizador a partir da sessão
$nome = $_SESSION['email'];

try {
    // Conecta à base de dados MySQL usando PDO
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca todos os exercícios disponíveis, ordenados por data de criação
    $stmt = $conn->query("
        SELECT id, titulo, descricao
        FROM exercicios
        ORDER BY data_criacao DESC
    ");
    $exercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Busca a pontuação total do aluno com base nas classificações feitas
    $id_aluno = $_SESSION['id_utilizador'];
    $pontuacao = $conn->prepare("
        SELECT SUM(pontos) AS total
        FROM classificacao
        WHERE id_utilizador = ?
    ");
    $pontuacao->execute([$id_aluno]);
    $total = $pontuacao->fetchColumn() ?? 0; // Caso não haja pontuação, assume 0

} catch (PDOException $e) {
    // Em caso de erro, termina o script e exibe a mensagem
    die("Erro: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Painel do Aluno</title>

  <!-- Importação dos estilos CSS -->
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/tabelas.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
</head>

<body>
  <!-- Cabeçalho do sistema -->
  <header>
    <div>
      <h1>Instituto de Educação</h1>
      <h2>Bem-vindo, aluno</h2>
    </div>

    <!-- Informação do utilizador e botão de logout -->
    <div class="user-info">
      <p><strong><?= htmlspecialchars($nome) ?></strong></p>
      <p><em>aluno</em></p>
      <a href="/projetor/logout.php">
        <button class="menu-button-header">Logout</button>
      </a>
    </div>
  </header>

  <!-- Estrutura principal com menu lateral e conteúdo -->
  <div class="layout-flex">
    
    <!-- Menu lateral de navegação -->
    <nav>
      <h2>Menu</h2>
      <ul>
        <li><a href="/projetor/aluno/painel_aluno.php">Painel</a></li>
        <li><a href="/projetor/aluno/dashboard_aluno.php">Ver Progresso</a></li>
      </ul>
    </nav>

    <!-- Conteúdo principal -->
    <main class="conteudo">

      <!-- Exibição da pontuação total acumulada pelo aluno -->
      <h2>Pontuação Total: <?= $total ?> ponto<?= $total != 1 ? 's' : '' ?></h2>

      <!-- Título da secção de exercícios -->
      <h3>Exercícios Disponíveis</h3>

      <!-- Se não houver exercícios -->
      <?php if (count($exercicios) === 0): ?>
        <p>Nenhum exercício disponível no momento.</p>
      
      <!-- Se houver exercícios -->
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Título</th>
              <th>Descrição</th>
              <th>Ação</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($exercicios as $e): ?>
              <tr>
                <td><?= htmlspecialchars($e['titulo']) ?></td>
                <td><?= htmlspecialchars($e['descricao']) ?></td>
                <td>
                  <a href="/projetor/responder_exercicio.php?id=<?= $e['id'] ?>">
                    <button>Responder</button>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </main>
  </div>

  <!-- Rodapé do sistema -->
  <footer>
    <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
  </footer>
</body>
</html>

