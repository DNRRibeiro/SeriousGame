//\projetor\js\analise_inquerito_filtros.js
// Executa o código apenas após o carregamento completo do DOM
document.addEventListener("DOMContentLoaded", () => {

  // Verifica se os dados foram definidos no PHP e passados para JS
  if (typeof dados === "undefined" || typeof frequencias === "undefined") return;

  // Extrai os nomes dos campos avaliados
  const campos = Object.keys(dados);

  // === GRÁFICO DE FREQUÊNCIA PARA CADA CAMPO ===
  campos.forEach(campo => {
    const ctx = document.getElementById("grafico_" + campo); // procura o <canvas>
    if (!ctx) return; // se não existir, pula para o próximo campo

    const valores = [1, 2, 3, 4, 5]; // opções possíveis da escala
    const respostas = valores.map(v => frequencias[campo][v] ?? 0); // total de respostas por valor

    // Cria o gráfico de barras com as frequências
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: valores.map(v => v.toString()), // ["1", "2", ..., "5"]
        datasets: [{
          label: "Frequência das respostas",
          data: respostas, // dados de frequência
          backgroundColor: 'rgba(59, 130, 246, 0.6)',
          borderColor: 'rgba(37, 99, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false } // oculta legenda para gráfico individual
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 } // valores inteiros no eixo Y
          }
        }
      }
    });
  });

  // === GRÁFICO GERAL DE MÉDIAS ===
  const graficoMedia = document.getElementById("grafico_media_geral");
  if (!graficoMedia) return;

  // Cria gráfico com médias por campo
  new Chart(graficoMedia, {
    type: 'bar',
    data: {
      labels: campos.map(c => 
        c.replace(/_/g, " ").replace(/\b\w/g, l => l.toUpperCase()) // Formata nomes (ex: "conteudo_util" -> "Conteudo Util")
      ),
      datasets: [{
        label: "Média",
        data: campos.map(c => dados[c].media ?? 0), // valores de média por campo
        backgroundColor: 'rgba(16, 185, 129, 0.5)',
        borderColor: 'rgba(5, 150, 105, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          max: 5 // escala fixa até 5 (escala de avaliação)
        }
      }
    }
  });
});
