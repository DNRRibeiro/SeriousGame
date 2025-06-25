<?php
session_start();

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: /projetor/index.html");
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];
$exercicio_id = $_POST['id_exercicio'] ?? null;
$respostas = $_POST['respostas'] ?? [];

if (!$exercicio_id || !is_array($respostas)) {
    exit("Dados inválidos.");
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=pythonr", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar perguntas
    $stmt = $conn->prepare("SELECT * FROM perguntas WHERE id_exercicio = ?");
    $stmt->execute([$exercicio_id]);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pontos_totais = 0;
    foreach ($perguntas as $i => $p) {
        $correta = trim(strtolower($p['resposta']));
        $aluno = trim(strtolower($respostas[$i] ?? ''));
        if ($correta === $aluno) {
            $pontos_totais += (int)$p['pontos'];
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO classificacao (id_utilizador, id_exercicio, pontos, data_hora)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$id_utilizador, $exercicio_id, $pontos_totais]);

    // Redireciona de volta com sucesso
    header("Location: responder_exercicio.php?id=$exercicio_id&sucesso=1&pontos=$pontos_totais");
    exit;

} catch (PDOException $e) {
    exit("Erro na base de dados: " . $e->getMessage());
}
?>