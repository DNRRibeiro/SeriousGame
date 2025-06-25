<!---\projetor\admin\remover_utilizador.php--->
<!--serve eiminiar utilizadores -->
<?php
// Inicia a sessão para aceder às variáveis da sessão
session_start();

// Verifica se o utilizador é admin (apenas administradores podem remover utilizadores)
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: /projetor/index.html");
    exit;
}

// Verifica se foi fornecido um ID válido por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}

// Converte o ID recebido para inteiro
$id = (int)$_GET['id'];

try {
    // Estabelece ligação à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Elimina todas as classificações relacionadas com o utilizador
    $stmt = $conn->prepare("DELETE FROM classificacao WHERE id_utilizador = ?");
    $stmt->execute([$id]);

    // Elimina o próprio utilizador da tabela de utilizadores
    $stmt = $conn->prepare("DELETE FROM utilizadores WHERE id = ?");
    $stmt->execute([$id]);

    // Redireciona para a página de acessos com uma mensagem de sucesso
    header("Location: /projetor/listas/listar_acessos.php?removido=1");
    exit;

} catch (PDOException $e) {
    // Em caso de erro na base de dados, apresenta mensagem informativa
    die("Erro ao remover utilizador: " . $e->getMessage());
}
?>