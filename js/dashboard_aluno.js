//\projetor\js\dashboard_aluno.js

function toggleFamilia(button) {
    const lista = button.nextElementSibling;
    const seta = button.querySelector('.seta');
    lista.classList.toggle('aberta');
    seta.textContent = lista.classList.contains('aberta') ? '▲' : '▼';
  }
  