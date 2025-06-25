<!---\projetor\admin\criar_familia.php--->
<!--serve para criar as familias dos exercicios-->
<?php
// Início da sessão para aceder às variáveis de sessão
session_start();

// Verifica se o utilizador está autenticado como 'professor' ou 'admin'
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    // Redireciona para a página inicial se não tiver permissão
    header("Location: /projetor/index.html");
    exit;
}

// Guarda o tipo de utilizador e o email
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];

// Trata o envio do formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtém e limpa os dados submetidos
    $nome = trim($_POST["nome"] ?? '');
    $descricao = trim($_POST["descricao"] ?? '');
    $ordem = (int)($_POST["ordem"] ?? 0);

    // Verifica se o campo nome foi preenchido
    if ($nome !== '') {
        try {
            // Conexão à base de dados
            $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Inserção da nova família na base de dados
            $stmt = $conn->prepare(
                "INSERT INTO familias (nome, descricao, ordem, criador_id, data_criacao) 
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$nome, $descricao, $ordem, $_SESSION['id_utilizador']]);

            // Redireciona para a listagem após criação
            header("Location: listar_familias.php");
            exit;
        } catch (PDOException $e) {
            // Mostra mensagem de erro em caso de falha
            $erro = "Erro ao gravar: " . $e->getMessage();
        }
    } else {
        // Mensagem caso o nome não tenha sido preenchido
        $erro = "O nome da família é obrigatório.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Criar Família</title>
  <!-- Inclusão das folhas de estilo -->
 
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
  <link rel="stylesheet" href="/projetor/css/listar_familias.css">
  <link rel="stylesheet" href="/projetor/css/formulario.css">
</head>
<body class="pagina-flex">

<!-- Cabeçalho com informação institucional e do utilizador -->
<header class="header-principal">
  <div class="header-esquerda">
    <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
    <h1>Instituto de Educação</h1>
  </div>

  <div class="header-centro">
    <h1>Criar Nova Família</h1>
  </div>

  <!-- Informações do utilizador -->
  <div class="user-info">
    <p><strong><?= htmlspecialchars($nome) ?></strong></p>
    <p><em><?= $tipo ?></em></p>
    <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
  </div>
</header>

<!-- Área principal da página com painel lateral -->
<div class="layout-flex">
  <!-- Navegação lateral -->
  <nav>
    <h2>Menu</h2>
    <ul>
      <li><a href="/projetor/admin/painel.php">⬅ Voltar ao Painel</a></li>
      <li><a href="/projetor/admin/listar_familias.php">Listar Famílias</a></li>
    </ul>
  </nav>

  <!-- Área de criação da nova família -->
  <main class="conteudo">
    <div class="familia-bloco">
      <h3>Nova Família</h3>

      <!-- Mostra mensagem de erro, se existir -->
      <?php if (!empty($erro)): ?>
        <p class="erro-mensagem"><?= htmlspecialchars($erro) ?></p>
      <?php endif; ?>

      <!-- Formulário para criar uma nova família -->
      <form method="POST" class="form-familia">
        <!-- Campo do nome da família -->
        <div>
          <label for="nome" class="label">Nome da Família:</label>
          <input type="text" id="nome" name="nome" required class="input-text">
        </div>

        <!-- Campo da descrição da família -->
        <div>
          <label for="descricao" class="label">Descrição:</label>
          <textarea id="descricao" name="descricao" rows="4" class="input-text"></textarea>
        </div>

        <!-- Campo da ordem de apresentação -->
        <div>
          <label for="ordem" class="label">Ordem de Apresentação:</label>
          <input type="number" name="ordem" id="ordem" min="0" required class="input-text">
        </div>

        <!-- Botão de submissão -->
        <div>
          <br>
          <button type="submit" class="menu-button">Criar Família</button>
        </div>
      </form>
    </div>
  </main>
</div>

<!-- Rodapé da aplicação -->
<footer>
  <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
</footer>

</body>
</html>


