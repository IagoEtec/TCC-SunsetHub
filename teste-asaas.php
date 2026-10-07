<?php
// PÁGINA SOMENTE PARA TESTE LOCAL. APAGUE antes de publicar o site.
// Simula um usuário logado (ID 1) para os endpoints funcionarem.
session_start();
$_SESSION['usuario_id'] = 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Teste Asaas</title>
<style>
  body { font-family: Arial, sans-serif; max-width: 640px; margin: 30px auto; padding: 0 12px; }
  fieldset { margin-bottom: 20px; padding: 14px; }
  input, select, button { padding: 8px; margin: 4px 0; width: 100%; box-sizing: border-box; }
  button { cursor: pointer; }
  pre { background: #f4f4f4; padding: 10px; overflow-x: auto; white-space: pre-wrap; }
  img { max-width: 240px; }
</style>
</head>
<body>
<h2>Teste da integração Asaas (sandbox)</h2>

<fieldset>
  <legend>1) Cadastrar cliente</legend>
  <input id="nome" placeholder="Nome" value="João da Silva">
  <input id="cpf" placeholder="CPF válido (só números)">
  <input id="email" placeholder="E-mail" value="joao@email.com">
  <button onclick="cadastrar()">Cadastrar cliente</button>
</fieldset>

<fieldset>
  <legend>2) Gerar cobrança PIX</legend>
  <select id="plano">
    <option value="mensal">Mensal - R$ 50,00</option>
    <option value="anual">Anual - R$ 480,00</option>
  </select>
  <button onclick="cobrar()">Gerar cobrança</button>
  <img id="qr" alt="">
  <p id="copia"></p>
</fieldset>

<h3>Resposta do servidor:</h3>
<pre id="saida">Aguardando...</pre>

<script>
async function enviar(url, corpo) {
  const saida = document.getElementById('saida');
  try {
    const r = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(corpo)
    });
    const texto = await r.text();
    saida.textContent = 'HTTP ' + r.status + '\n' + texto;
    try { return JSON.parse(texto); } catch (e) { return null; }
  } catch (e) {
    saida.textContent = 'Erro de rede: ' + e;
    return null;
  }
}

function cadastrar() {
  enviar('client-asaas.php', {
    nome: document.getElementById('nome').value,
    cpfCnpj: document.getElementById('cpf').value,
    email: document.getElementById('email').value
  });
}

async function cobrar() {
  const d = await enviar('cobranca-asaas.php', { plano: document.getElementById('plano').value });
  if (d && d.ok) {
    document.getElementById('qr').src = d.pixImagem || '';
    document.getElementById('copia').textContent = d.pixCopiaECola ? 'Copia e cola: ' + d.pixCopiaECola : '';
  }
}
</script>
</body>
</html>