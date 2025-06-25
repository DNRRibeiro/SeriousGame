<!---\projetor\admin\listar_familias.php--->
<!--serve consultar familias de exercicios criadas -->
<?php
// Inicia a sessão
session_start();

// Verifica se o utilizador tem permissões para aceder (apenas professor ou admin)
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

// Obtém dados da sessão
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

try {
    // Conecta à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta que junta famílias com os exercícios criados e quem os criou
    $stmt = $conn->query("SELECT f.*, u.nome AS criador_nome FROM familias f LEFT JOIN utilizadores u ON f.criador_id = u.id ORDER BY f.ordem ASC, f.data_criacao ASC");

    $familias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Listar Famílias</title>
  <link rel="stylesheet" href="/projetor/style.css">
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
    <h1>Listar Famílias</h1>
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
      <li><a href="/projetor/admin/criar_familia.php">+ Nova Família</a></li>
    </ul>
  </nav>

  <main class="conteudo">
    <?php if (count($familias) === 0): ?>
      <p>Nenhuma família criada ainda.</p>
    <?php else: ?>
      <?php foreach ($familias as $familia): ?>
        <div class="familia-bloco">
          <h3><?= htmlspecialchars($familia['nome']) ?> <small>(Ordem: <?= htmlspecialchars($familia['ordem']) ?>)</small></h3>
          <p><?= nl2br(htmlspecialchars($familia['descricao'])) ?></p>

          <table class="tabela-exercicios">
            <thead>
              <tr>
                <th>Criador</th>
                <th>Data</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= htmlspecialchars($familia['criador_nome']) ?></td>
                <td><?= htmlspecialchars($familia['data_criacao']) ?></td>
                <td>
                  <a href="/projetor/admin/editar_familia.php?id=<?= $familia['id'] ?>" class="btn btn-editar">Editar</a>
                  <?php if ($_SESSION['tipo'] === 'admin'): ?>
                    <a href="/projetor/admin/excluir_familia.php?id=<?= $familia['id'] ?>" class="btn btn-excluir" onclick="return confirm('Tem a certeza que deseja apagar esta família?');">Apagar</a>
                  <?php endif; ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>

<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>
</body>
</html>
