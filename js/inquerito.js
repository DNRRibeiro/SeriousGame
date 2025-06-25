//\projetor\js\inquerito.js

document.addEventListener("DOMContentLoaded", () => {
  // Seletores de elementos do DOM
  const container = document.getElementById("perguntasContainer");
  const enviarBtn = document.getElementById("enviarBtn");
  const form = document.getElementById("inqueritoForm");
  const mensagem = document.getElementById("mensagem");

  // Lista de perguntas organizadas por grupo temático
  const perguntas = [
    { id: "rapidez", grupo: "Técnico", texto: "A plataforma é rápida e responde bem aos seus comandos?" },
    { id: "bugs", grupo: "Técnico", texto: "Você encontrou erros técnicos?" },
    { id: "multidispositivo", grupo: "Técnico", texto: "Funcionou bem em todos dispositivos?" },
    { id: "navegacao", grupo: "Técnico", texto: "A navegação é fluida e intuitiva?" },

    { id: "conteudo_util", grupo: "Pedagógico", texto: "Os conteúdos ajudam no aprendizado?" },
    { id: "organizacao", grupo: "Pedagógico", texto: "Organização dos tópicos é clara?" },
    { id: "nivel_dificuldade", grupo: "Pedagógico", texto: "A dificuldade está adequada?" },
    { id: "feedback", grupo: "Pedagógico", texto: "O feedback é útil e compreensível?" },

    { id: "visual", grupo: "Usabilidade", texto: "O visual da plataforma é agradável?" },
    { id: "facilidade_localizar", grupo: "Usabilidade", texto: "É fácil localizar informações?" },
    { id: "responder_exercicios", grupo: "Usabilidade", texto: "Responder aos exercícios é simples?" },
    { id: "autonomia", grupo: "Usabilidade", texto: "Sente-se confortável usando a plataforma sozinho?" },

    { id: "desempenho", grupo: "Satisfação Geral", texto: "A plataforma contribui para seu desempenho académico?" },
    { id: "recomendacao", grupo: "Satisfação Geral", texto: "Você recomendaria esta plataforma a outros?" }
  ];

  // Verifica se a variável global `jaRespondeu` foi definida e é verdadeira
  if (typeof jaRespondeu !== "undefined" && jaRespondeu) {
    mensagem.innerHTML = "<p class='aviso'>Você já respondeu este inquérito.</p>";

    // Desativa todos os inputs do formulário para impedir nova resposta
    form.querySelectorAll("input, textarea, button").forEach(el => el.disabled = true);
    return;
  }

  let grupoAtual = "";

  // Cria os elementos HTML dinamicamente para cada pergunta
  perguntas.forEach(p => {
    // Adiciona um novo título se o grupo mudar
    if (p.grupo !== grupoAtual) {
      const titulo = document.createElement("h3");
      titulo.textContent = p.grupo;
      container.appendChild(titulo);
      grupoAtual = p.grupo;
    }

    // Cria o bloco da pergunta
    const bloco = document.createElement("div");
    bloco.className = "grupo-radio";

    // Label da pergunta
    const pergunta = document.createElement("label");
    pergunta.textContent = p.texto;
    bloco.appendChild(pergunta);

    // Div com opções de resposta de 1 a 5 (rádio)
    const radios = document.createElement("div");
    radios.className = "radio-opcoes";

    for (let i = 1; i <= 5; i++) {
      const item = document.createElement("label");
      item.innerHTML = `<input type="radio" name="${p.id}" value="${i}" required> ${i}`;
      radios.appendChild(item);
    }

    // Monta o bloco no container
    bloco.appendChild(radios);
    container.appendChild(bloco);
  });
});
