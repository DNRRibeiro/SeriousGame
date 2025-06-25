<?php
// login.php
session_start();

// Cabeçalhos para CORS e JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Responde imediatamente a pedidos OPTIONS (pré-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Lê os dados enviados em JSON
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$pass = $data['pass'] ?? '';

// Conecta-se à base de dados
$conn = new mysqli("localhost", "root", "", "pythonr");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["sucesso" => false, "erro" => "Erro na ligação à base de dados"]);
    exit;
}

// Prepara a consulta para verificar utilizador
$stmt = $conn->prepare("SELECT id, tipo FROM utilizadores WHERE email = ? AND pass = ?");
$stmt->bind_param("ss", $email, $pass);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Guarda dados na sessão
    $_SESSION['logado'] = true;
    $_SESSION['email'] = $email;
    $_SESSION['id_utilizador'] = $row['id'];
    $_SESSION['tipo'] = $row['tipo'];

    // Registra o login na tabela login_log
    $ip = $_SERVER['REMOTE_ADDR']; // IP do utilizador
    $stmt_log = $conn->prepare("INSERT INTO login_log (id_utilizador, ip_address) VALUES (?, ?)");
    $stmt_log->bind_param("is", $row['id'], $ip);
    $stmt_log->execute();
    $stmt_log->close();

    // Resposta para o frontend
    echo json_encode([
        "sucesso" => true,
        "tipo" => $row['tipo']
    ]);
} else {
    // Credenciais inválidas
    http_response_code(401);
    echo json_encode(["sucesso" => false, "erro" => "Credenciais inválidas"]);
}

// Fecha conexões
$stmt->close();
$conn->close();
?>
