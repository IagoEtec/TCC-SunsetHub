import 'dotenv/config';
import Brapi from 'brapi';
import 'dotenv/config';

const brapi = new Brapi();

console.log(brapi);

const client = new Brapi({
  apiKey: process.env.BRAPI_API_KEY,
});

// Buscar cotação de uma ação
const quote = await client.quote.retrieve('PETR4');
console.log(quote.results[0].regularMarketPrice);

// Buscar múltiplas ações
const quotes = await client.quote.retrieve('PETR4,VALE3,ITUB4');
console.log(quotes.results);

const stocks = await client.quote.list();
// Com paginação
const stocksPage = await client.quote.list({
  page: 1,
  limit: 50,
});
console.log(stocks.stocks);
