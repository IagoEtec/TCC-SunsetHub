<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teste API brapi.dev - Select</title>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      background: #0e1116;
      color: #e6e8eb;
      margin: 0;
      padding: 40px 20px;
      min-height: 100vh;
    }
    .container { max-width: 640px; margin: 0 auto; }
    h1 { font-size: 1.6rem; margin-bottom: 4px; }
    .subtitle { color: #8b93a1; font-size: 0.95rem; margin-bottom: 28px; }
    .card {
      background: #161b22;
      border: 1px solid #262c36;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
    }
    label { display: block; font-size: 0.85rem; color: #8b93a1; margin-bottom: 6px; }
    
    select {
      width: 100%;
      padding: 10px 12px;
      background: #0e1116;
      border: 1px solid #303845;
      border-radius: 6px;
      color: #e6e8eb;
      font-size: 1rem;
      margin-bottom: 14px;
      cursor: pointer;
    }
    select:focus { outline: none; border-color: #4c8bf5; }

    input[type="number"] {
      width: 100%;
      padding: 10px 12px;
      background: #0e1116;
      border: 1px solid #303845;
      border-radius: 6px;
      color: #e6e8eb;
      font-size: 1rem;
      margin-bottom: 14px;
    }
    input[type="number"]:focus { outline: none; border-color: #4c8bf5; }

    .total-box {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      background: #0e1116;
      border: 1px solid #303845;
      border-radius: 6px;
      padding: 12px 14px;
      margin-top: 12px;
    }
    .total-box .label { color: #8b93a1; font-size: 0.9rem; }
    .total-box .value { font-size: 1.3rem; font-weight: 700; color: #3fb950; }
    
    button {
      background: #4c8bf5;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 10px 18px;
      font-size: 0.95rem;
      cursor: pointer;
      width: 100%;
    }
    button:hover { background: #3d78e0; }
    button:disabled { background: #303845; cursor: not-allowed; }

    #resultado { margin-top: 20px; }
    .quote { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; }
    .quote .ticker { font-size: 1.3rem; font-weight: 600; }
    .quote .price { font-size: 1.6rem; font-weight: 700; }
    .change { font-size: 0.95rem; margin-bottom: 16px; }
    .change.positive { color: #3fb950; }
    .change.negative { color: #f85149; }
    .details { font-size: 0.85rem; color: #8b93a1; border-top: 1px solid #262c36; padding-top: 12px; }
    .details div { display: flex; justify-content: space-between; padding: 4px 0; }
    .status { font-size: 0.9rem; padding: 10px 12px; border-radius: 6px; }
    .status.error { background: #2d1215; color: #f85149; border: 1px solid #4a1c1f; }
    .status.loading { color: #8b93a1; }
    pre {
      background: #0e1116;
      border: 1px solid #262c36;
      border-radius: 6px;
      padding: 12px;
      font-size: 0.78rem;
      overflow-x: auto;
      color: #8b93a1;
      margin-top: 16px;
    }
  </style>
    <link rel="stylesheet" href="css/liquid-glass.css?v=101">

</head>
<body>
  <div class="container">
    <h1>Escolha uma Ação</h1>
    <p class="subtitle">Selecione uma ação carregada do Brapi SDK no backend</p>

    <div class="card">
      <label for="selectStock">Selecione a ação:</label>
      <select id="selectStock">
        <option value="">Carregando ações...</option>
      </select>

      <label for="quantidade">Quantidade:</label>
      <input type="number" id="quantidade" value="1" min="1" step="1">

      <button id="btnBuscar" onclick="buscarCotacao()">Buscar cotação</button>
    </div>

    <div id="resultado"></div>
  </div>

  <script>
    const API_URL = 'http://localhost:3000/api';

    // Função executada ao carregar a página para preencher o SELECT
    async function carregarOpcoes() {
      const select = document.getElementById('selectStock');
      try {
        const response = await fetch(`${API_URL}/stocks`);
        const stocks = await response.json();

        select.innerHTML = '<option value="">-- Selecione uma ação --</option>';

        stocks.forEach(item => {
          const option = document.createElement('option');
          option.value = item.stock;
          option.textContent = `${item.stock} - ${item.name}`;
          select.appendChild(option);
        });
      } catch (err) {
        select.innerHTML = '<option value="">Erro ao carregar ações</option>';
        console.error('Erro ao listar ações:', err);
      }
    }

    // Guarda os dados da última cotação buscada, para recalcular o total
    // sempre que a quantidade mudar, sem precisar chamar a API de novo.
    let ultimaCotacao = null;

    // Função para consultar os dados da ação selecionada
    async function buscarCotacao() {
      const ticker = document.getElementById('selectStock').value;
      const resultadoDiv = document.getElementById('resultado');
      const btn = document.getElementById('btnBuscar');

      if (!ticker) {
        resultadoDiv.innerHTML = '<div class="status error">Por favor, selecione uma ação.</div>';
        ultimaCotacao = null;
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Buscando...';
      resultadoDiv.innerHTML = '<p class="status loading">Consultando API...</p>';

      try {
        const response = await fetch(`${API_URL}/quote/${ticker}`);
        const data = await response.json();

        if (!response.ok || !data.results || data.results.length === 0) {
          resultadoDiv.innerHTML = `<div class="status error">Erro ao buscar dados do ticker ${ticker}.</div>`;
          ultimaCotacao = null;
          return;
        }

        const q = data.results[0];
        ultimaCotacao = q; // guarda para o cálculo do total
        renderizarResultado(q);
      } catch (err) {
        resultadoDiv.innerHTML = `<div class="status error">Erro na requisição: ${err.message}</div>`;
        ultimaCotacao = null;
      } finally {
        btn.disabled = false;
        btn.textContent = 'Buscar cotação';
      }
    }

    // Desenha o card de resultado, incluindo o total (preço x quantidade)
    function renderizarResultado(q) {
      const resultadoDiv = document.getElementById('resultado');
      const quantidade = Math.max(1, parseInt(document.getElementById('quantidade').value, 10) || 1);
      const preco = Number(q.regularMarketPrice) || 0;
      const total = preco * quantidade;
      const moeda = q.currency || 'R$';

      const change = q.regularMarketChangePercent || 0;
      const changeClass = change >= 0 ? 'positive' : 'negative';
      const changeSign = change >= 0 ? '+' : '';

      resultadoDiv.innerHTML = `
        <div class="card">
          <div class="quote">
            <span class="ticker">${q.symbol}</span>
            <span class="price">${moeda} ${preco.toFixed(2)}</span>
          </div>
          <div class="change ${changeClass}">
            ${changeSign}${Number(q.regularMarketChange || 0).toFixed(2)} (${changeSign}${Number(change).toFixed(2)}%)
          </div>
          <div class="total-box">
            <span class="label">${q.symbol} x ${quantidade}</span>
            <span class="value">${moeda} ${total.toFixed(2)}</span>
          </div>
          <div class="details">
            <div><span>Nome</span><span>${q.shortName || q.longName || '-'}</span></div>
            <div><span>Abertura</span><span>${q.regularMarketOpen ?? '-'}</span></div>
            <div><span>Máxima</span><span>${q.regularMarketDayHigh ?? '-'}</span></div>
            <div><span>Mínima</span><span>${q.regularMarketDayLow ?? '-'}</span></div>
            <div><span>Volume</span><span>${q.regularMarketVolume ?? '-'}</span></div>
          </div>
          <pre>${JSON.stringify(q, null, 2)}</pre>
        </div>
      `;
    }

    // Recalcula o total instantaneamente ao mudar a quantidade,
    // sem precisar buscar a cotação de novo na API.
    document.getElementById('quantidade').addEventListener('input', () => {
      if (ultimaCotacao) {
        renderizarResultado(ultimaCotacao);
      }
    });

    // Inicializa a busca das ações ao carregar
    carregarOpcoes();
  </script>

    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="JS/acessibilidade.js" defer></script>
    <script src="JS/liquid-glass.js?v=99" defer></script>
</body>
</html>

