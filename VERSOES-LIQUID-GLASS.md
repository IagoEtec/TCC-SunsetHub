# Evolução Liquid Glass — v69 a v83

| Versão | Alteração | Justificativa |
|---:|---|---|
| 69 | Tokens visuais compartilhados | Garantir que todas as páginas usem a mesma linguagem material. |
| 70 | Fundo atmosférico em camadas | Criar profundidade sem imagens pesadas ou dependências externas. |
| 71 | Superfícies translúcidas com blur e saturação | Reproduzir o efeito de vidro fosco do material de referência. |
| 72 | Borda especular e brilho interno | Dar volume às superfícies e separar cartões do fundo. |
| 73 | Botões, inputs e foco unificados | Fazer a interface inteira parecer um único sistema, sem perder acessibilidade. |
| 74 | Luz especular acompanha o ponteiro | Fazer o vidro responder espacialmente ao mouse. |
| 75 | Sheen aquático contínuo no hover | Adicionar movimento suave parecido com reflexo na água. |
| 76 | Ripple no clique | Tornar a interação perceptível sem alterar a ação do elemento. |
| 77 | Inclinação 3D sutil | Criar resposta de profundidade proporcional à posição do ponteiro. |
| 78 | Campos de formulário integrados | Evitar que formulários pareçam componentes de outro site. |
| 79 | Navbar e dropdowns conectados | Manter navegação, páginas e menus dentro do mesmo material. |
| 80 | Blur responsivo e fallback de movimento | Reduzir custo visual em telas pequenas e respeitar preferências do usuário. |
| 81 | Tabelas e cards utilitários integrados | Cobrir também áreas densas de dados e administrações. |
| 82 | Tratamento específico para toque | Evitar efeitos de ponteiro caros em dispositivos sem mouse. |
| 83 | Foco acessível, limpeza e QA | Concluir o ciclo com teclado, redução de movimento, caminhos validados e sem bloquear cliques. |

A implementação fica em `css/liquid-glass.css` e `JS/liquid-glass.js`. As rotas PHP que não têm HTML próprio foram mantidas como endpoints e includes; elas não são páginas visuais e não devem carregar CSS ou JavaScript.


# Evolução Liquid Glass — v84 a v98

| Versão | Alteração | Justificativa |
|---:|---|---|
| 84 | Transição suave de entrada e saída | Evitar cortes secos ao abrir ou deixar páginas. |
| 85 | Reveal por visibilidade | Fazer páginas longas surgirem conforme o conteúdo entra na tela. |
| 86 | Controles magnéticos | Aproximar levemente botões e links do cursor, reforçando a sensação física. |
| 87 | Estado ativo de navegação | Identificar a página atual com luz e sublinhado de vidro. |
| 88 | Movimento lento dos blobs atmosféricos | Criar um fundo vivo sem distrair do conteúdo. |
| 89 | Corrente de luz no fundo | Simular deslocamento contínuo de luz pela superfície. |
| 90 | Elevação óptica refinada | Aumentar profundidade apenas durante interação. |
| 91 | Linhas de tabela responsivas | Dar retorno espacial também em dados densos. |
| 92 | Halo de foco | Melhorar localização por teclado sem depender do mouse. |
| 93 | Marcador da navegação atual | Conectar orientação e estética no menu principal. |
| 94 | Gates de performance para toque e redução de movimento | Evitar custo de eventos e animações onde não agregam. |
| 95 | Transição de links respeitando modificadores | Preservar Ctrl/Cmd, Shift, Alt, botão direito e abas novas. |
| 96 | Surfaces reveladas pelo IntersectionObserver | Reduzir trabalho inicial e manter ritmo visual em páginas extensas. |
| 97 | Ajustes para mobile | Reduzir inclinação e deslocamento em telas estreitas. |
| 98 | Coerência final e cache busting | Garantir que as 28 páginas recebam o motor atualizado. |


# Refinamento v99

| Versão | Alteração | Justificativa |
|---:|---|---|
| 99 | Luzes brancas suavizadas, material unificado, fundos conflitantes da navbar neutralizados, parallax amortecido, orbe discreto seguindo o mouse e tilt reduzido | Deixar todas as páginas visualmente coerentes, elegantes e menos brilhantes, com movimento contínuo sem competir com o conteúdo ou bloquear a interação. |


# Revisão Liquid Glass — v100

| Versão | Alteração | Justificativa |
|---:|---|---|
| 100 | Material global aplicado também a `modulo-card`, `aula-item`, `quiz-card`, `item-card`, cards de saldo e caixas de conteúdo | Corrigir a ausência visual do Liquid Glass nas páginas de videoaulas, exercícios e loja sem remover os estilos específicos existentes. |
| 100 | Descoberta automática de cards por seletores semânticos e classes reutilizáveis | Fazer a mesma experiência alcançar as demais páginas, inclusive componentes criados em CSS interno. |
| 100 | Parallax de rolagem com amortecimento, tilt suave pelo ponteiro, brilho contextual e ripple | Dar profundidade e resposta física aos cards sem exagerar no movimento. |
| 100 | Ícones e títulos dos cards receberam microparallax no hover | Criar hierarquia visual e sensação de materialidade. |
| 100 | Navegação e dropdowns tiveram fundos internos neutralizados | Corrigir os fundos de cor conflitantes observados na navbar. |
| 100 | Cache global atualizado de v99 para v100 em 21 páginas visuais | Garantir que o navegador carregue os assets novos. |


# Revisão de navegação e progressão — v101

| Área | Alteração | Justificativa |
|---|---|---|
| Navbar | Inclusão nas páginas internas que não exibiam o componente | Manter orientação e acesso ao perfil em todo o site autenticado. |
| Perfil | Foto real buscada do banco na navbar | Evitar que o menu mostre apenas a inicial depois do upload. |
| Educação | Videoaulas, exercícios e loja retornam para `aprendizado.php` | Criar um fluxo educacional previsível e consistente. |
| Patente | Patente em destaque com superfície Liquid Glass | Tornar a conquista visível e compreensível. |
| XP | Novo campo persistido, barra de progresso e distância até a próxima patente | Mostrar claramente a evolução do usuário. |
| Navbar | Correção do link quebrado de Investimentos | Restaurar o dropdown e a navegação entre os conteúdos. |


# Ajuste v102 — retornos e cartão de patente

| Área | Alteração | Justificativa |
|---|---|---|
| Videoaulas | Botão superior corrigido de `loja.php` para `aprendizado.php` | Evitar que o usuário seja enviado para a loja ao tentar voltar. |
| Educação | Textos internos padronizados para “Voltar” | Reduzir ruído visual e manter os controles objetivos. |
| Perfil | Linha de XP reorganizada como “XP de treino — atual / meta” | Aproximar a leitura do exemplo enviado e tornar a progressão imediata. |
