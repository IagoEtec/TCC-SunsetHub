# LEIA-ME PARA ARRUMAR TUDO

## Escopo desta rodada

Este relatório reúne apenas problemas identificados fora do funcionamento direto do Sunset Hub e que, conforme solicitado, **não foram alterados**. Os itens que afetam a entrada ou o uso da comunidade foram corrigidos no projeto integrado: campo de senha do login, validação de senha do cadastro, destino do botão Google e atalho Sunset Hub no cabeçalho móvel.

## Problemas confirmados e como corrigir

### 1. A navegação geral do portal desaparece no celular

**Onde:** `css/navbar.css`, regra `@media (max-width: 768px)` (a regra `nav { display: none; }`). O cabeçalho não implementa um botão hambúrguer para reabrir esse menu. O atalho móvel **Comunidade** foi adicionado separadamente para preservar o acesso ao Sunset Hub, mas os outros destinos principais continuam ocultos em telas estreitas.

**Como corrigir:** implementar um botão de menu móvel na navbar, com `aria-expanded`, abertura/fechamento por clique e tecla Escape, foco visível e navegação por teclado. Exibir os links existentes em painel acessível e fechar o painel após a escolha de uma rota. Testar pelo menos em 320 px, 768 px e desktop.

### 2. O controle de notificações aponta para um endpoint que não existe

**Onde:** `front-end/configuracoes.php` aponta para `back-end/atualizar-notificacoes.php`, mas esse arquivo não está presente na cópia do projeto.

**Como corrigir:** criar o endpoint autenticado para salvar a preferência no banco, validar o token CSRF da sessão e devolver estado/erro de forma consistente; alternativamente, retirar ou desabilitar o controle até haver persistência implementada. Confirmar que a configuração volta marcada corretamente depois de sair e entrar.

### 3. Exportação e exclusão de conta apontam para rotas ausentes

**Onde:** `front-end/privacidade.php` oferece `back-end/exportar-dados.php` e `back-end/excluir-conta.php`, que não estão presentes na cópia verificada. A página referencia `../js/modais.js`, mas esse arquivo não existe; portanto, não há nessa cópia um controlador local confirmado para abrir/fechar o modal. A ação final também aponta para rota inexistente.

**Como corrigir:** implementar a exportação autenticada como download CSV, limitada ao usuário da sessão e com cabeçalhos de download seguros. Para exclusão, criar um `POST` autenticado com CSRF e confirmação explícita no servidor; revogar/limpar integrações e aplicar a política de retenção antes de remover dados. Não usar um link `GET` para uma operação destrutiva. Até implementar, não apresentar esses controles como operacionais.

### 4. Imagens de investimentos ausentes

**Onde:** `front-end/investimentos_RendaVariavel.php` referencia `front-end/img/acoes.png`, `fiis.png`, `etfs.png` e `bdr.png`, mas esses arquivos não existem nesses caminhos.

**Como corrigir:** colocar as quatro imagens no caminho esperado ou ajustar os `src` para arquivos existentes em `img/`, confirmando o caminho relativo à URL da página.

### 5. Os planos pagos enviam para uma tela de checkout inexistente

**Onde:** `front-end/planos.php` abre o modal dos planos Premium e envia para `front-end/checkout.php`; esse arquivo não existe na cópia verificada. A página também não inclui a navbar compartilhada no HTML atual.

**Como corrigir:** implementar o checkout com o provedor autorizado, validação server-side do plano e preço, proteção CSRF, retorno e webhook idempotentes; nunca confiar no valor enviado pelo navegador. Enquanto não houver checkout real e testado em sandbox, desabilitar a assinatura paga ou indicar que está indisponível. A inclusão da navbar pode ser decidida separadamente para essa página.

## Limites e reteste funcional pendente

A auditoria estática desta rodada encontrou **76 arquivos PHP sem erro de sintaxe** e **9 arquivos JavaScript sem erro de sintaxe**. Uma varredura local de rotas e recursos segue registrada nos itens acima; os módulos fora do Sunset Hub não foram alterados.

O `C:/xampp/mysql/bin/my.ini` local contém `port=3307`, mas nesta rodada não havia listener acessível na porta. A conexão MySQL e a abertura de sockets locais para o servidor de teste foram bloqueadas por erro de permissão/conectividade 10013; por isso o navegador não conseguiu carregar a cópia local e o MariaDB isolado de QA não pôde iniciar. A sintaxe e a consistência estática foram verificadas, mas **não foi possível certificar login real, leitura/gravação no banco, uploads MP4, cliques em todos os botões ou navegação autenticada completa**. Essa limitação é do ambiente de execução desta sessão, não um resultado de teste funcional bem-sucedido.

Para concluir o reteste em um XAMPP acessível: iniciar Apache e MySQL e confirmar `DB_HOST=127.0.0.1`, `DB_PORT=3307`, `DB_NAME=educacaofinanceira` e as credenciais corretas em `.env` (sem sobrescrever a senha local). O bootstrap importa o dump principal somente quando o banco está vazio; em banco existente com `usuarios`, cria as tabelas Sunset faltantes sem migração separada. A conta precisa poder executar `CREATE`. Depois, executar os fluxos de login, cadastro, rotas, configurações, privacidade, carteira, pagamentos em sandbox e Sunset Hub. Não use dados financeiros reais para esses testes.

## Não ativado

Autenticação e sincronização direta com Discord, Instagram e YouTube continuam fora do escopo: dependem de credenciais OAuth/API e permissões dos serviços. O Sunset Hub integrado oferece amizades, mensagens privadas, vídeos MP4, publicações visuais, stories temporários e grupos; chamadas de vídeo continuam reservadas, sem conexão de câmera/microfone.
