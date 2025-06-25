<!---\projetor\admin\remover_exercicio.php--->
<!--serve eiminiar exercicios -->
<?php
// Inicia a sessão para aceder às variáveis da sessão
session_start();

// Verifica se o utilizador tem permissões (professor ou admin)
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['professor', 'admin'])) {
    header("Location: /projetor/index.html");
    exit;
}

// Obtém o ID do exercício a remover
$id = $_GET['id'] ?? null;

// Verifica se o ID foi fornecido
if (!$id) {
    exit("ID do exercício não fornecido.");
}

try {
    // Conexão à base de dados
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se o exercício existe
    $stmt = $conn->prepare("SELECT id FROM exercicios WHERE id = ?");
    $stmt->execute([$id]);

    // Caso o exercício não exista, interrompe a execução
    if ($stmt->rowCount() === 0) {
        exit("Exercício não encontrado.");
    }

    // Remove primeiro os registos associados ao exercício, por ordem de dependência
    $conn->prepare("DELETE FROM perguntas WHERE id_exercicio = ?")->execute([$id]);
    $conn->prepare("DELETE FROM videos WHERE id_exercicio = ?")->execute([$id]);
    $conn->prepare("DELETE FROM imagens WHERE id_exercicio = ?")->execute([$id]);

    // Remove o próprio exercício
    $conn->prepare("DELETE FROM exercicios WHERE id = ?")->execute([$id]);

    // Redireciona após a remoção com mensagem de sucesso
    header("Location: listar_exercicios.php?msg=Exercício removido com sucesso");
    exit;

} catch (PDOException $e) {
    // Em caso de erro, apresenta mensagem informativa
    die("Erro ao remover exercício: " . $e->getMessage());
}
?>

