//\projetor\js\valida_imagem.js
// Executa após o carregamento do DOM
document.addEventListener('DOMContentLoaded', () => {

  // Seleciona o campo de input para upload de imagens
  const imagemInput = document.querySelector('input[name="imagem_upload[]"]');

  // Quando o utilizador selecionar arquivos
  imagemInput.addEventListener('change', function () {
    
    // Para cada imagem selecionada
    for (const file of this.files) {
      const img = new Image(); // cria elemento de imagem vazio
      const objectUrl = URL.createObjectURL(file); // cria URL temporária para visualizar

      // Quando a imagem for carregada
      img.onload = function () {
        // Verifica se a dimensão é exatamente 1920x1080
        if (this.width !== 1920 || this.height !== 1080) {
          // Alerta o utilizador e limpa o input
          alert(`A imagem "${file.name}" deve ter exatamente 1920x1080 píxeis. Será ignorada.`);
          imagemInput.value = ''; // limpa a seleção inteira
        }

        // Libera o recurso da memória
        URL.revokeObjectURL(objectUrl);
      };

      // Define o src para acionar o carregamento
      img.src = objectUrl;
    }
  });
});

  