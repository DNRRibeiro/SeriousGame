 <!-- \projetor\inquerito\anailise_inquerito.php -->
<?php
session_start();

// Verifica se utilizador está autenticado como aluno
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: /projetor/index.html");
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];

// Verifica se já respondeu ao inquérito
try {
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM inqueritos WHERE id_utilizador = ?");
    $stmt->execute([$id_utilizador]);
    if ($stmt->fetchColumn() > 0) {
        echo "<p>Você já respondeu ao inquérito. Redirecionando...</p>";
        header("refresh:3;url=/projetor/aluno/dashboard_aluno.php");
        exit;
    }

    // Lista de campos esperados
    $campos = [
        "rapidez", "bugs", "multidispositivo", "navegacao",
        "conteudo_util", "organizacao", "nivel_dificuldade", "feedback",
        "visual", "facilidade_localizar", "responder_exercicios", "autonomia",
        "desempenho", "recomendacao", "comentarios"
    ];

    // Validação básica
    foreach ($campos as $campo) {
        if ($campo !== "comentarios" && (!isset($_POST[$campo]) || !in_array($_POST[$campo], ["1", "2", "3", "4", "5"]))) {
            die("Erro: campo inválido - " . htmlspecialchars($campo));
        }
    }

    // Prepara e executa inserção
    $sql = "INSERT INTO inqueritos (
        id_utilizador, rapidez, bugs, multidispositivo, navegacao,
        conteudo_util, organizacao, nivel_dificuldade, feedback,
        visual, facilidade_localizar, responder_exercicios, autonomia,
        desempenho, recomendacao, comentarios
    ) VALUES (
        :id_utilizador, :rapidez, :bugs, :multidispositivo, :navegacao,
        :conteudo_util, :organizacao, :nivel_dificuldade, :feedback,
        :visual, :facilidade_localizar, :responder_exercicios, :autonomia,
        :desempenho, :recomendacao, :comentarios
    )";

    $stmt = $conn->prepare($sql);
    $dados = [];
    foreach ($campos as $campo) {
        $dados[$campo] = $campo === "comentarios" ? htmlspecialchars($_POST[$campo]) : (int)$_POST[$campo];
    }
    $dados['id_utilizador'] = $id_utilizador;

    $stmt->execute($dados);

    echo "<p>Obrigado pela sua participação! Redirecionando...</p>";
    header("refresh:3;url=/projetor/aluno/dashboard_aluno.php");

} catch (PDOException $e) {
    die("Erro ao salvar inquérito: " . $e->getMessage());
}
?>
