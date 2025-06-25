<?php
// logout.php
session_start();

// Destrói todas as variáveis da sessão
$_SESSION = [];
session_unset();
session_destroy();

// Redireciona para a página de login
header("Location: /projetor/index.html");
exit;
?>
