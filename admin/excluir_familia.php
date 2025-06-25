<!---\projetor\admin\excluir_familia.php--->
<!--serve para eliminar as familias dos exercicios-->
<?php
// Início da sessão para validação do utilizador
session_start();

// Verifica se o utilizador é administrador
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

// Verifica se foi fornecido um ID de família
$id = $_GET['id'] ?? null;
if (!$id) {
    exit("ID da família não fornecido.");
}

try {
    // Estabelece a ligação à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se existem exercícios associados à família
    $check = $conn->prepare("SELECT COUNT(*) FROM exercicios WHERE id_familia = ?");
    $check->execute([$id]);
    $total = $check->fetchColumn();

    // Impede a eliminação caso existam exercícios associados
    if ($total > 0) {
        exit("Não é possível apagar esta família pois existem exercícios associados.");
    }

    // Elimina a família da base de dados
    $delete = $conn->prepare("DELETE FROM familias WHERE id = ?");
    $delete->execute([$id]);

    // Redireciona de volta à lista de famílias após a eliminação
    header("Location: /projetor/admin/listar_familias.php");
    exit;

} catch (PDOException $e) {
    // Mensagem de erro caso a operação falhe
    exit("Erro ao eliminar: " . $e->getMessage());
}

