//\projetor\js\progresso_exercicio.js

// Executa o script assim que o conteúdo da página for carregado
document.addEventListener("DOMContentLoaded", () => {

  // Seleciona todos os campos de input com classe .input-resposta
  const inputs = document.querySelectorAll(".input-resposta");

  // Seleciona a barra que será preenchida visualmente
  const barra = document.getElementById("barraPreenchida");

  // Seleciona o texto que mostra "X de Y respondidas"
  const texto = document.getElementById("textoProgresso");

  // Número total de perguntas (inputs)
  const total = inputs.length;

  // Função que atualiza a barra de progresso e o texto
  function atualizarProgresso() {
    let preenchidas = 0;

    // Conta quantos inputs têm valor preenchido
    inputs.forEach(input => {
      if (input.value.trim() !== "") preenchidas++;
    });

    // Calcula a percentagem
    const percentagem = (preenchidas / total) * 100;

    // Atualiza a largura da barra visual
    barra.style.width = percentagem + "%";

    // Atualiza o texto descritivo
    texto.textContent = `${preenchidas} de ${total} respondidas`;
  }

  // Adiciona um listener para cada campo: sempre que o aluno digita, atualiza o progresso
  inputs.forEach(input => {
    input.addEventListener("input", atualizarProgresso);
  });

  // Executa a função no carregamento inicial (útil para formulários preenchidos anteriormente)
  atualizarProgresso();
});

  