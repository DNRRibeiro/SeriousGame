<!---\projetor\admin\listar_exercicios.php--->
<!--serve consultar exercicios criados -->
<?php
// Inicia a sessão para aceder às variáveis de sessão
session_start();

// Garante que o utilizador tem permissão de acesso (professor ou admin)
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

// Obtém informação da sessão
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

try {
    // Ligação à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta à base de dados para buscar famílias e respetivos exercícios
    $stmt = $conn->query("
    SELECT 
        f.id AS familia_id, f.nome AS familia_nome, f.descricao AS familia_descricao, f.ordem AS familia_ordem,
        e.id AS exercicio_id, e.titulo, e.data_criacao, e.ordem AS exercicio_ordem,
        u.nome AS criador_nome
    FROM familias f
    LEFT JOIN exercicios e ON e.id_familia = f.id
    LEFT JOIN utilizadores u ON e.criador_id = u.id
    ORDER BY f.ordem ASC, f.data_criacao ASC, e.ordem ASC, e.data_criacao ASC
    ");

    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organização dos exercícios por família
    $familias = [];
    foreach ($dados as $row) {
        $fid = $row['familia_id'];
        if (!isset($familias[$fid])) {
            $familias[$fid] = [
                'nome' => $row['familia_nome'],
                'descricao' => $row['familia_descricao'],
                'exercicios' => []
            ];
        }

        // Adiciona os exercícios à respetiva família
        if ($row['exercicio_id']) {
            $familias[$fid]['exercicios'][] = [
                'id' => $row['exercicio_id'],
                'titulo' => $row['titulo'],
                'data_criacao' => $row['data_criacao'],
                'criador' => $row['criador_nome'],
                'ordem' => $row['exercicio_ordem']
            ];
        }
    }

} catch (PDOException $e) {
    die("Erro ao buscar exercícios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Listar Exercícios por Família</title>
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/listar_familias.css">
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
    <h1>Listar Exercícios por Família</h1>
  </div>

  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<div class="layout-flex">
  <nav>
    <h2>Menu</h2>
    <ul>
      <li><a href="/projetor/admin/painel.php">⬅ Voltar ao Painel</a></li>
      <li><a href="/projetor/admin/criar_exercicio.php">+ Novo Exercício</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <?php if (empty($familias)): ?>
      <p>Nenhuma família com exercícios foi encontrada.</p>
    <?php else: ?>
      <?php foreach ($familias as $familia): ?>
        <section class="familia-bloco">
          <h3><?= htmlspecialchars($familia['nome']) ?></h3>
          <p><?= nl2br(htmlspecialchars($familia['descricao'])) ?></p>

          <?php if (empty($familia['exercicios'])): ?>
            <p><em>Sem exercícios nesta família.</em></p>
          <?php else: ?>
            <table class="tabela-exercicios">
              <thead>
                <tr>
                  <th>Ordem</th>
                  <th>Título</th>
                  <th>Data</th>
                  <th>Criador</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($familia['exercicios'] as $ex): ?>
                  <tr>
                    <td><?= $ex['ordem'] ?></td>
                    <td><?= htmlspecialchars($ex['titulo']) ?></td>
                    <td><?= $ex['data_criacao'] ?></td>
                    <td><?= htmlspecialchars($ex['criador']) ?></td>
                    <td>
                      <a href="/projetor/admin/ver_exercicio.php?id=<?= $ex['id'] ?>" class="btn">Ver</a>
                      <a href="/projetor/admin/editar_exercicio.php?id=<?= $ex['id'] ?>" class="btn btn-editar">Editar</a>
                      <?php if ($_SESSION['tipo'] === 'admin'): ?>
                        <a href="/projetor/admin/remover_exercicio.php?id=<?= $ex['id'] ?>" class="btn btn-excluir" onclick="return confirm('Apagar este exercício?');">Apagar</a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </section>
        <hr>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>

<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>
</body>
</html>
