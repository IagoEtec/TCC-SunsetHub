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
    'btn' => 'Assinar Premium', 'link' => '#', 'primario' => true,
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
    'btn' => 'Assinar Premium Pro', 'link' => '#', 'primario' => true,
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
  <?php include_once __DIR__ . "/navbar.php"; ?>



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

      <p class="garantia">Planos pagos em preparação · Nenhuma cobrança é processada nesta versão</p>

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
            <p>Ainda não. A alteração de plano ficará disponível quando o checkout for integrado.</p>
          </details>
          <details>
            <summary>Existe fidelidade ou multa de cancelamento?</summary>
            <p>Os planos pagos ainda não estão disponíveis para contratação.</p>
          </details>
          <details>
            <summary>Quais formas de pagamento são aceitas?</summary>
            <p>Nenhuma forma de pagamento está habilitada nesta versão.</p>
          </details>
          <details>
            <summary>O que acontece com meus dados se eu voltar ao plano Gratuito?</summary>
            <p>Seus dados são mantidos. Apenas os recursos exclusivos dos planos pagos ficam indisponíveis.</p>
          </details>
        </div>
      </section>

    </div>
  </main>

  <div class="modal-overlay" id="modalCompra" hidden><div class="modal-compra" role="dialog" aria-modal="true" aria-labelledby="mcTitulo"><button type="button" class="mc-fechar" aria-label="Fechar">&times;</button><span class="badge">Aviso</span><h2 id="mcTitulo">Assinaturas pagas indisponíveis</h2><p>O checkout ainda não está implementado e nenhuma cobrança pode ser processada com segurança. Não informe número de cartão, CVV ou CPF nesta versão.</p><button type="button" class="mc-fechar btn-plano">Entendi</button></div></div>
  <script>
    var ciclo='mensal';
    function setCiclo(c){ciclo=c;document.querySelectorAll('.ciclo button').forEach(function(b){var active=b.dataset.ciclo===c;b.classList.toggle('ativo',active);b.setAttribute('aria-pressed',String(active));});document.querySelectorAll('.plano-card [data-mensal]:not(.btn-assinar)').forEach(function(el){el.textContent=el.dataset[c];});}
    document.querySelectorAll('.ciclo button').forEach(function(b){b.addEventListener('click',function(){setCiclo(b.dataset.ciclo);});});
    var modal=document.getElementById('modalCompra');document.querySelectorAll('.btn-assinar').forEach(function(b){b.addEventListener('click',function(){modal.hidden=false;modal.querySelector('.mc-fechar').focus();});});
    modal.addEventListener('click',function(e){if(e.target===modal||e.target.closest('.mc-fechar'))modal.hidden=true;});document.addEventListener('keydown',function(e){if(e.key==='Escape')modal.hidden=true;});
  </script>
<script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>