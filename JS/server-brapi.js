import express from 'express';
import cors from 'cors';
import 'dotenv/config';
import Brapi from 'brapi';

const app = express();

// Problema: cors() sem configuração libera "Access-Control-Allow-Origin: *"
// para qualquer site da internet, permitindo que outros sites consumam
// a cota da chave BRAPI_API_KEY configurada neste servidor às custas do
// projeto (abuso de recursos), mesmo sem envolver dados de usuário.
// Solução: restringe a origens explicitamente permitidas, configuráveis
// via variável de ambiente ALLOWED_ORIGIN (lista separada por vírgulas);
// em desenvolvimento local, o padrão cobre o front-end servido pelo
// XAMPP/Laragon em localhost/127.0.0.1.
const origensPermitidas = (process.env.ALLOWED_ORIGIN || 'http://localhost,http://127.0.0.1')
  .split(',')
  .map((origem) => origem.trim());

app.use(cors({ origin: origensPermitidas }));
app.use(express.json());

const client = new Brapi({
  apiKey: process.env.BRAPI_API_KEY,
});

// Rota para listar todas as ações
app.get('/api/stocks', async (req, res) => {
  try {
    // Busca as ações usando o SDK da Brapi
    const response = await client.quote.list({
      limit: 100, // Ajuste a quantidade conforme necessário
    });

    // Retorna a lista de ações para o frontend
    res.json(response.stocks);
  } catch (error) {
    console.error('Erro ao buscar lista de ações:', error);
    res.status(500).json({ error: 'Erro ao buscar ações' });
  }
});

// Rota para consultar detalhes de uma ação selecionada
app.get('/api/quote/:ticker', async (req, res) => {
  try {
    const { ticker } = req.params;
    const response = await client.quote.retrieve(ticker);
    res.json(response);
  } catch (error) {
    console.error('Erro ao buscar cotação:', error);
    res.status(500).json({ error: 'Erro ao buscar cotação' });
  }
});

const PORT = 3000;
app.listen(PORT, '127.0.0.1', () => {
  console.log(`Servidor rodando em http://localhost:${PORT}`);
});
