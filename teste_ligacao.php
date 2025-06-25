<!--\projeto\teste_ligacao.php-->
<?php
$servidor = "localhost";
$utilizador = "root";
$senha = ""; 
$base_dados = "pythonr"; 

// Criar ligação
$conexao = new mysqli($servidor, $utilizador, $senha, $base_dados);

// Verificar ligação
if ($conexao->connect_error) {
    die("Falha na ligação: " . $conexao->connect_error);
} else {
    echo "Ligação bem-sucedida à base de dados!";
}

// Fechar ligação
$conexao->close();
?>
