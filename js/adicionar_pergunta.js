// /projetor/js/adicionar_pergunta.js
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btn-adicionar-pergunta').addEventListener('click', () => {
      const container = document.getElementById('perguntas-container');
      const item = document.createElement('div');
      item.className = 'pergunta-item';
      item.innerHTML = `
        <input type="text" name="perguntas[]" placeholder="Pergunta" required>
        <input type="text" name="respostas[]" placeholder="Resposta" required>
        <input type="number" name="pontos[]" placeholder="Pontos" value="1" min="1" required>
      `;
      container.appendChild(item);
    });
  });
  