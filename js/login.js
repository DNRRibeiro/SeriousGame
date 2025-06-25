// Adiciona um ouvinte ao formulário de login para capturar o evento de envio
// e evitar que a página recarregue ao submeter o formulário

document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  // Obtém os valores introduzidos nos campos de email e palavra-passe
  const email = document.getElementById("email").value;
  const pass = document.getElementById("pass").value;

  // Envia os dados para o ficheiro PHP via fetch com método POST
  fetch("/projetor/login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email: email, pass: pass })
  })
    .then(res => res.json()) // Converte a resposta JSON
    .then(data => {
      console.log("Resposta do login.php:", data); // Mostra no console o JSON retornado do PHP

      if (data.sucesso) {
        // Redireciona consoante o tipo de utilizador recebido do PHP
        switch (data.tipo) {
          case "admin":
          case "professor":
            window.location.href = "/projetor/admin/painel.php";
            break;
          case "aluno":
            window.location.href = "/projetor/aluno/dashboard_aluno.php";
            break;
          default:
            document.getElementById("mensagem").innerHTML =
              "<p style='color:red;'>Tipo de utilizador desconhecido: " + data.tipo + "</p>";
        }
      } else {
        // Mensagem de erro caso as credenciais estejam erradas
        document.getElementById("mensagem").innerHTML =
          "<p style='color:red;'>Credenciais inválidas.</p>";
      }
    })
    .catch(error => {
      // Mostra erro se a requisição falhar por motivos técnicos (ex: servidor offline)
      console.error("Erro no login:", error);
      document.getElementById("mensagem").innerHTML =
        "<p style='color:red;'>Erro ao tentar fazer login.</p>";
    });
});
