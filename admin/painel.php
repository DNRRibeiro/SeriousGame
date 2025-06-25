<!---\projetor\admin\painel.php--->
<!--painel de navegação professor e adminstrador -->
<?php
session_start();

// Verifica se o utilizador tem permissão para aceder ao painel (apenas professores e administradores)
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

// Recupera os dados da sessão
$tipo = $_SESSION['tipo'];
$nome = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Painel de Controlo</title>
  <!-- Inclusão dos ficheiros de estilos do sistema -->
  <link rel="stylesheet" href="/projetor/css/painel.css">
  <link rel="stylesheet" href="/projetor/css/header.css">
  <link rel="stylesheet" href="/projetor/css/base_layout.css">
</head>
<body>
  <!-- Cabeçalho com identidade da escola e dados do utilizador -->
  <header class="header-principal">
    <div class="header-esquerda">
      <img src="/projetor/imagens/logo.png" alt="Logotipo" class="logo-header">
      <h1>Instituto de Educação</h1>
    </div>

    <div class="header-centro">
      <h1>Painel de Controlo</h1>
    </div>

    <div class="user-info">
      <p><strong><?= htmlspecialchars($nome) ?></strong></p>
      <p><em><?= $tipo ?></em></p>
      <a href="/projetor/logout.php"><button class="menu-button-header">Logout</button></a>
    </div>
  </header>

  <!-- Estrutura com menu lateral e área de conteúdo -->
  <div style="display: flex;">
    <nav>
      <h2>Painel</h2>
      <ul>
        <li><a href="/projetor/admin/painel.php">Início do Painel</a></li>       
        <li><a href="/projetor/admin/listar_exercicios.php">Gerir Exercícios</a></li>
        <li><a href="/projetor/admin/listar_familias.php">Gerir Famílias</a></li>
        <li><a href="/projetor/listas/listar_acessos_filtros.php">Filtrar Submissões</a></li>
        <li><a href="/projetor/listas/lista_totais_filtros.php">Scoreboard com Filtros</a></li>
        <li><a href="/projetor/inquerito/analise_inquerito.php">Análise de Inquérito</a></li>
        <li><a href="/projetor/inquerito/analise_inquerito_filtros.php">Inquérito com Filtros</a></li>
        <!-- Opções exclusivas para o administrador -->
        <?php if ($tipo === 'admin'): ?>
          <li><a href="/projetor/listas/logins_recentes.php">Logins Recentes</a></li>
          <li><a href="/projetor/listas/listar_acessos.php">Listar Todos os Acessos</a></li>
          <li><a href="/projetor/admin/gerir_utilizadores.php">Gerir Utilizadores</a></li>
          
        <?php endif; ?>
      </ul>
    </nav>

    <!-- Área principal de conteúdo do painel -->
    <main class="conteudo">
      <h2>Funções Disponíveis</h2>
      <p>Utilize o menu lateral para aceder às funcionalidades administrativas do sistema.</p>
    </main>
  </div>

  <!-- Rodapé da página -->
  <footer>
    <p>&copy; 2025 - Sistema de Gestão de Exercícios</p>
  </footer>
</body>
</html>
