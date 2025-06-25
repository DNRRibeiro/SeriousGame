let pyodideReady = loadPyodide();

async function avaliarRespostas() {
  const pyodide = await pyodideReady;
  let total = 0;
  let max = document.querySelectorAll("[data-esperado]").length;

  document.querySelectorAll("[data-esperado]").forEach((textarea, index) => {
    const esperado = textarea.dataset.esperado.trim();
    const feedback = document.getElementById(`feedback${index}`);
    const outputBox = document.getElementById(`output${index}`);

    try {
      pyodide.runPython("import sys, io; sys.stdout = io.StringIO()");
      pyodide.runPython(textarea.value);
      const output = pyodide.runPython("sys.stdout.getvalue().strip()");
      outputBox.textContent = "Saída do código:\n" + output;

      if (String(output).trim().toLowerCase() === esperado.toLowerCase()) {
        feedback.textContent = "✅ Resposta correta";
        feedback.className = "feedback correto";
        total++;
      } else {
        feedback.textContent = "❌ Resposta errada.";
        feedback.className = "feedback errado";
      }
    } catch (e) {
      feedback.textContent = "Erro: " + e;
      feedback.className = "feedback errado";
      outputBox.textContent = "";
    }
  });

  const resultado = document.getElementById("resultado-final");
  resultado.textContent = `Pontos obtidos: ${total} / ${max}`;
}
