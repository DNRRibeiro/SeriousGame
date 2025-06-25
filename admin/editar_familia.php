<!---\projetor\admin\editar_familia.php--->
<!--serve para editar as familias dos já criadas-->
<?php
session_start();

// Verificação de permissões de acesso
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

// Verifica se foi passado um ID de família
if (!isset($_GET['id'])) {
    exit("ID de família não especificado.");
}

$id_familia = $_GET['id'];
$mensagem = "";

try {
    // Conexão à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar os dados atuais da família
    $stmt = $conn->prepare("SELECT * FROM familias WHERE id = ?");
    $stmt->execute([$id_familia]);
    $familia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$familia) {
        exit("Família não encontrada.");
    }

    // Processa o formulário se este for submetido
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $ordem = (int) ($_POST['ordem'] ?? 0);

        $update = $conn->prepare("UPDATE familias SET nome = ?, descricao = ?, ordem = ? WHERE id = ?");
        $update->execute([$nome, $descricao, $ordem, $id_familia]);

        $mensagem = "<p class='sucesso'>Família atualizada com sucesso!</p>";

        // Atualiza variáveis locais para refletir as mudanças
        $familia['nome'] = $nome;
        $familia['descricao'] = $descricao;
        $familia['ordem'] = $ordem;
    }
} catch (PDOException $e) {
    $mensagem = "<p class='erro'>Erro: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Editar Família</title>
  <link rel="stylesheet" href="/projetor/css/painel.css">

  <link rel="stylesheet" href="/projetor/css/formulario.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
</head>
<body>

<!-- Cabeçalho com informação do utilizador -->
<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
    <h1>Instituto de Educação</h1>
  </div>

  <div class="header-centro">
    <h1>Editar Família</h1>
  </div>

  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<!-- Formulário de edição da família -->
<main class="conteudo">
  <?= $mensagem ?>
  <form method="POST" class="formulario">
    <label for="nome">Nome da Família:</label>
    <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($familia['nome']) ?>" required>

    <label for="descricao">Descrição:</label>
    <textarea name="descricao" id="descricao" rows="4"><?= htmlspecialchars($familia['descricao']) ?></textarea>

    <label for="ordem">Ordem de Apresentação:</label>
    <input type="number" name="ordem" id="ordem" value="<?= htmlspecialchars($familia['ordem']) ?>" min="0" required>
    <br><br>
    <button type="submit" class="menu-button">Guardar Alterações</button>
    <br><br>
    <a href="/projetor/admin/listar_familias.php"><button type="button">⬅ Voltar ao Painel</button></a>
  </form>
</main>

<!-- Rodapé -->
<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>

</body>
</html>
