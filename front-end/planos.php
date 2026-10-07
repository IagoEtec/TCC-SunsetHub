<?php
// planos.php – Página de Planos
// Ajuste preços, recursos e links de checkout no array abaixo.

$planos = [
  [
    'id' => 'gratuito', 'nome' => 'Gratuito', 'icone' => '🌱', 'classe' => '',
    'desc' => 'Para quem está começando a organizar a vida financeira.',
    'mensal' => 0, 'anual' => 0,
    'btn' => 'Começar grátis', 'link' => 'cadastro.php', 'primario' => false,
    'recursos' => [
      ['Carteira de investimentos (até 5 ativos)', true],
      ['Calculadora financeira', true],
      ['Controle de receitas e despesas', true],
      ['Analisador de ativos completo', false],
      ['Relatórios avançados e exportação', false],
      ['Suporte prioritário', false],
    ],
  ],
  [
    'id' => 'premium', 'nome' => 'Premium', 'icone' => '🚀', 'classe' => 'popular', 'tag' => 'Mais popular',
    'desc' => 'Ferramentas completas para investir com mais segurança.',
    'mensal' => 19.90, 'anual' => 15.90,
    'btn' => 'Assinar Premium', 'link' => 'checkout.php?plano=premium', 'primario' => true,
    'recursos' => [
      ['Tudo do plano Gratuito', true],
      ['Carteira ilimitada de ativos', true],
      ['Analisador de ativos completo', true],
      ['Relatórios avançados e exportação', true],
      ['Sem anúncios', true],
      ['Suporte prioritário', false],
    ],
  ],
  [
    'id' => 'pro', 'nome' => 'Premium Pro', 'icone' => '👑', 'classe' => 'pro',
    'desc' => 'Para investidores que querem o máximo de análise e controle.',
    'mensal' => 39.90, 'anual' => 31.90,
    'btn' => 'Assinar Premium Pro', 'link' => 'checkout.php?plano=pro', 'primario' => true,
    'recursos' => [
      ['Tudo do plano Premium', true],
      ['Múltiplas carteiras', true],
      ['Alertas personalizados de preço', true],
      ['Simulações e projeções avançadas', true],
      ['Acesso antecipado a novidades', true],
      ['Suporte prioritário', true],
    ],
  ],
];

function preco_partes(float $v): array {
  $f = number_format($v, 2, ',', '.');
  return explode(',', $f);
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="claro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Planos | FinControl</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/liquid-glass.css">
  <link rel="stylesheet" href="../css/style-plano.css">
</head>
<body class="planos-page">

  <?php // include 'includes/navbar.php'; // <- sua navbar padrão ?>

  <main class="planos-main">

    <!-- HERO -->
    <section class="hero-planos">
      <span class="badge">Planos e preços</span>
      <h1>Escolha o plano ideal para <span class="destaque">o seu momento</span></h1>
      <p>Comece de graça e evolua quando quiser. Sem fidelidade, cancele a qualquer momento.</p>

      <div class="ciclo" role="group" aria-label="Ciclo de cobrança">
        <button type="button" class="ativo" data-ciclo="mensal" aria-pressed="true">Mensal</button>
        <button type="button" data-ciclo="anual" aria-pressed="false">Anual <small>-20%</small></button>
      </div>
    </section>

    <div class="content">

      <!-- CARDS -->
      <section class="planos-grid">
        <?php foreach ($planos as $p):
          [$im, $cm] = preco_partes($p['mensal']);
          [$ia, $ca] = preco_partes($p['anual']);
          $gratis = $p['mensal'] == 0;
        ?>
        <article class="plano-card <?= htmlspecialchars($p['classe']) ?>">
          <?php if (!empty($p['tag'])): ?>
            <span class="plano-tag"><?= htmlspecialchars($p['tag']) ?></span>
          <?php endif; ?>

          <h2><?= htmlspecialchars($p['nome']) ?></h2>
          <p class="plano-desc"><?= htmlspecialchars($p['desc']) ?></p>

          <div class="plano-preco">
            <span class="moeda">R$</span>
            <span class="valor" data-mensal="<?= $im ?>" data-anual="<?= $ia ?>"><?= $im ?></span>
            <span class="centavos" data-mensal=",<?= $cm ?>" data-anual=",<?= $ca ?>">,<?= $cm ?></span>
          </div>
          <p class="plano-periodo"
             data-mensal="<?= $gratis ? 'Grátis para sempre' : 'por mês' ?>"
             data-anual="<?= $gratis ? 'Grátis para sempre' : 'por mês, cobrado anualmente' ?>">
             <?= $gratis ? 'Grátis para sempre' : 'por mês' ?></p>

          <?php if ($gratis): ?>
            <a href="<?= htmlspecialchars($p['link']) ?>" class="btn-plano"><?= htmlspecialchars($p['btn']) ?></a>
          <?php else: ?>
            <button type="button" class="btn-plano primario btn-assinar"
                    data-plano="<?= htmlspecialchars($p['id']) ?>"
                    data-nome="<?= htmlspecialchars($p['nome']) ?>"
                    data-mensal="<?= $p['mensal'] ?>" data-anual="<?= $p['anual'] ?>">
              <?= htmlspecialchars($p['btn']) ?>
            </button>
          <?php endif; ?>

          <ul class="plano-lista">
            <?php foreach ($p['recursos'] as [$texto, $ativo]): ?>
              <li class="<?= $ativo ? '' : 'off' ?>"><?= htmlspecialchars($texto) ?></li>
            <?php endforeach; ?>
          </ul>
        </article>
        <?php endforeach; ?>
      </section>

      <p class="garantia">Pagamento seguro · Garantia de 7 dias · Cancele quando quiser</p>

      <!-- COMPARATIVO -->
      <section class="secao">
        <h2>Compare os planos</h2>
        <div class="tabela-wrap">
          <table class="tabela">
            <thead>
              <tr><th>Recurso</th><th>Gratuito</th><th class="dest">Premium</th><th>Premium Pro</th></tr>
            </thead>
            <tbody>
              <tr><td>Ativos na carteira</td><td>Até 5</td><td>Ilimitado</td><td>Ilimitado</td></tr>
              <tr><td>Número de carteiras</td><td>1</td><td>1</td><td>Múltiplas</td></tr>
              <tr><td>Calculadora financeira</td><td class="sim">✓</td><td class="sim">✓</td><td class="sim">✓</td></tr>
              <tr><td>Analisador de ativos completo</td><td class="nao">—</td><td class="sim">✓</td><td class="sim">✓</td></tr>
              <tr><td>Relatórios e exportação</td><td class="nao">—</td><td class="sim">✓</td><td class="sim">✓</td></tr>
              <tr><td>Alertas de preço</td><td class="nao">—</td><td class="nao">—</td><td class="sim">✓</td></tr>
              <tr><td>Simulações avançadas</td><td class="nao">—</td><td class="nao">—</td><td class="sim">✓</td></tr>
              <tr><td>Suporte prioritário</td><td class="nao">—</td><td class="nao">—</td><td class="sim">✓</td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- FAQ -->
      <section class="secao">
        <h2>Perguntas frequentes</h2>
        <div class="faq">
          <details>
            <summary>Posso trocar de plano depois?</summary>
            <p>Sim. Você pode fazer upgrade ou downgrade a qualquer momento pelo seu perfil, e o valor é ajustado proporcionalmente.</p>
          </details>
          <details>
            <summary>Existe fidelidade ou multa de cancelamento?</summary>
            <p>Não. Você cancela quando quiser e continua com acesso até o fim do período já pago.</p>
          </details>
          <details>
            <summary>Quais formas de pagamento são aceitas?</summary>
            <p>Cartão de crédito e Pix. Os pagamentos são processados em ambiente seguro.</p>
          </details>
          <details>
            <summary>O que acontece com meus dados se eu voltar ao plano Gratuito?</summary>
            <p>Seus dados são mantidos. Apenas os recursos exclusivos dos planos pagos ficam indisponíveis.</p>
          </details>
        </div>
      </section>

    </div>
  </main>

  <!-- MODAL DE COMPRA -->
  <div class="modal-overlay" id="modalCompra" hidden>
    <div class="modal-compra" role="dialog" aria-modal="true" aria-labelledby="mcTitulo">
      <button type="button" class="mc-fechar" aria-label="Fechar">&times;</button>

      <form action="checkout.php" method="post" id="formCompra" autocomplete="off">
        <input type="hidden" name="plano" id="mcPlano">
        <input type="hidden" name="ciclo" id="mcCiclo" value="mensal">
        <input type="hidden" name="metodo" id="mcMetodo" value="cartao">

        <span class="badge">Compra segura</span>
        <h2 id="mcTitulo">Assinar <span id="mcNome">Premium</span></h2>

        <div class="mc-resumo">
          <div class="ciclo mc-ciclo" role="group" aria-label="Ciclo de cobrança">
            <button type="button" class="ativo" data-ciclo="mensal">Mensal</button>
            <button type="button" data-ciclo="anual">Anual <small>-20%</small></button>
          </div>
          <div class="mc-total">
            <span id="mcPeriodo">Total por mês</span>
            <strong id="mcValor">R$ 0,00</strong>
          </div>
        </div>

        <div class="mc-metodos" role="tablist">
          <button type="button" class="ativo" data-metodo="cartao">Cartão</button>
          <button type="button" data-metodo="pix">Pix</button>
        </div>

        <div class="mc-form-cartao" id="mcCartao">
          <div class="input-group"><label for="mcNomeCartao">Nome no cartão</label>
            <input id="mcNomeCartao" name="nome_cartao" placeholder="Como está impresso" required></div>
          <div class="input-group"><label for="mcNumero">Número do cartão</label>
            <input id="mcNumero" name="numero" inputmode="numeric" placeholder="0000 0000 0000 0000" maxlength="19" required></div>
          <div class="mc-linha">
            <div class="input-group"><label for="mcValidade">Validade</label>
              <input id="mcValidade" name="validade" inputmode="numeric" placeholder="MM/AA" maxlength="5" required></div>
            <div class="input-group"><label for="mcCvv">CVV</label>
              <input id="mcCvv" name="cvv" inputmode="numeric" placeholder="123" maxlength="4" required></div>
          </div>
          <div class="input-group"><label for="mcCpf">CPF do titular</label>
            <input id="mcCpf" name="cpf" inputmode="numeric" placeholder="000.000.000-00" maxlength="14" required></div>
        </div>

        <div class="mc-pix" id="mcPix" hidden>
          <p>Ao confirmar, geramos um <strong>QR Code Pix</strong> com o valor do plano. A assinatura é liberada assim que o pagamento é aprovado.</p>
        </div>

        <button type="submit" class="btn-plano primario mc-confirmar">Confirmar assinatura</button>
        <p class="mc-nota">Cancele quando quiser · Garantia de 7 dias</p>
      </form>
    </div>
  </div>

  <script>
    var ciclo = 'mensal', planoAtual = null;
    var brl = function (v) { return v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); };

    // Aplica o ciclo (mensal/anual) em toda a página e no modal
    function setCiclo(c) {
      ciclo = c;
      document.querySelectorAll('.ciclo button').forEach(function (b) {
        var ativo = b.dataset.ciclo === c;
        b.classList.toggle('ativo', ativo);
        b.setAttribute('aria-pressed', ativo);
      });
      document.querySelectorAll('.plano-card [data-mensal]:not(.btn-assinar)').forEach(function (el) {
        el.textContent = el.dataset[c];
      });
      document.getElementById('mcCiclo').value = c;
      if (planoAtual) {
        var v = parseFloat(planoAtual.dataset[c]);
        document.getElementById('mcValor').textContent = brl(c === 'anual' ? v * 12 : v);
        document.getElementById('mcPeriodo').textContent = c === 'anual' ? 'Total por ano (12x ' + brl(v) + ')' : 'Total por mês';
      }
    }
    document.querySelectorAll('.ciclo button').forEach(function (b) {
      b.addEventListener('click', function () { setCiclo(b.dataset.ciclo); });
    });

    // Modal
    var modal = document.getElementById('modalCompra');
    var ultimoFoco = null;
    function abrirModal(btn) {
      planoAtual = btn; ultimoFoco = btn;
      document.getElementById('mcPlano').value = btn.dataset.plano;
      document.getElementById('mcNome').textContent = btn.dataset.nome;
      setCiclo(ciclo);
      modal.hidden = false;
      document.body.style.overflow = 'hidden';
      setTimeout(function () { document.getElementById('mcNomeCartao').focus(); }, 50);
    }
    function fecharModal() {
      modal.hidden = true;
      document.body.style.overflow = '';
      if (ultimoFoco) ultimoFoco.focus();
    }
    document.querySelectorAll('.btn-assinar').forEach(function (b) {
      b.addEventListener('click', function () { abrirModal(b); });
    });
    modal.addEventListener('click', function (e) { if (e.target === modal) fecharModal(); });
    modal.querySelector('.mc-fechar').addEventListener('click', fecharModal);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) fecharModal(); });

    // Cartão x Pix
    document.querySelectorAll('.mc-metodos button').forEach(function (b) {
      b.addEventListener('click', function () {
        var pix = b.dataset.metodo === 'pix';
        document.querySelectorAll('.mc-metodos button').forEach(function (x) { x.classList.toggle('ativo', x === b); });
        document.getElementById('mcMetodo').value = b.dataset.metodo;
        document.getElementById('mcCartao').hidden = pix;
        document.getElementById('mcPix').hidden = !pix;
        document.querySelectorAll('#mcCartao input').forEach(function (i) { i.required = !pix; });
      });
    });

    // Máscaras
    function mascara(id, fn) {
      document.getElementById(id).addEventListener('input', function (e) { e.target.value = fn(e.target.value.replace(/\D/g, '')); });
    }
    mascara('mcNumero', function (v) { return v.slice(0, 16).replace(/(\d{4})(?=\d)/g, '$1 '); });
    mascara('mcValidade', function (v) { return v.slice(0, 4).replace(/(\d{2})(?=\d)/, '$1/'); });
    mascara('mcCvv', function (v) { return v.slice(0, 4); });
    mascara('mcCpf', function (v) {
      return v.slice(0, 11).replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    });
  </script>
<script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>