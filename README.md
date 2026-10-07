# PTCC-EducacaoFinanceira

## Configuração do MySQL no XAMPP

O projeto está configurado para conectar ao MySQL/MariaDB do XAMPP pela porta **3307**. Verifique `C:/xampp/mysql/bin/my.ini` e mantenha `port=3307`; inicie Apache e MySQL. Se já existir `.env`, preserve as credenciais e ajuste `DB_HOST=127.0.0.1`, `DB_PORT=3307` e `DB_NAME=educacaofinanceira`. Caso não exista, copie `.env.example` para `.env` e configure `DB_USER`/`DB_PASS` conforme o seu XAMPP. Crie o banco `educacaofinanceira` no phpMyAdmin se ele ainda não existir. Na primeira conexão, o sistema carrega `educacaofinanceira.sql` automaticamente **somente se o banco estiver totalmente vazio**; esse dump já inclui o Sunset Hub. Bancos não vazios nunca são sobrescritos; em um banco do TCC existente, apenas tabelas comunitárias faltantes são criadas automaticamente.

## Acessibilidade

O widget de acessibilidade (`JS/acessibilidade.js`) está integrado em todas as páginas do site
(`front-end/*.php` e `admin/*.php`), sendo carregado antes do fechamento de `</body>`.

Funcionalidades do widget:
- Ajuste de tamanho de fonte (diminuir, padrão, aumentar)
- Filtros de simulação de daltonismo (protanopia, deuteranopia, tritanopia, acromatopsia)
- Modo de alto contraste
- Preferências salvas no `localStorage` do navegador
- Painel acessível via teclado (Esc fecha, foco automático) e leitor de tela (ARIA)



## Calendário financeiro integrado ao Open Finance

A carteira agora sincroniza as transações da conta conectada pela Pluggy e salva os lançamentos em `open_finance_transacoes`. O calendário combina esses registros com as movimentações manuais, mostra o mês atual do primeiro dia até o dia corrente e oferece navegação pelos meses desde a criação da conta. O gráfico histórico compara receitas e despesas mês a mês.

O dump único `educacaofinanceira.sql` já inclui a tabela `open_finance_transacoes`; não é necessário executar SQL adicional. Configure `PLUGGY_CLIENT_ID` e `PLUGGY_CLIENT_SECRET` como variáveis de ambiente do PHP. O botão de conexão mantém `includeSandbox: true` para permitir contas de teste durante o desenvolvimento; em produção, essa opção deve ser desativada quando não houver necessidade de conectores sandbox.

A categorização automática usa a categoria retornada pela Pluggy e regras simples baseadas na descrição, operação e comerciante. Na carteira, cada transação importada possui um seletor para o usuário corrigir a categoria; essa escolha fica marcada como manual e é preservada nas sincronizações seguintes. As categorias disponíveis incluem **Alimentação**, **Transporte**, **Moradia**, **Contas**, **Saúde**, **Educação**, **Lazer** e **Outros**.

A sincronização é acionada ao abrir ou atualizar os dados da carteira. A integração usa o endpoint cursor-based `/v2/transactions` com páginas de até 500 registros e mantém fallback para o endpoint legado. A Pluggy disponibiliza normalmente até 12 meses de histórico por produto de transações; o gráfico mostra todos os meses existentes no cadastro e, para meses sem lançamentos, exibe zero.

## Perfil, foto e banner

A página `front-end/perfil.php` permite editar os dados pessoais, trocar a foto com um editor em formato **1:1**, aplicar zoom e reposicionar a imagem arrastando-a, além de enviar um banner para o cabeçalho do perfil. Bancos criados com uma versão anterior recebem a coluna `banner` automaticamente ao abrir o perfil; bancos antigos recebem a atualização automaticamente pela aplicação.

## Uploads no XAMPP

Os uploads são gravados em `uploads/perfis` e `uploads/banners`. Essas pastas precisam existir e ter permissão de escrita para o usuário do Apache. O banner é enviado automaticamente assim que um arquivo é escolhido; a foto de perfil é salva como JPEG quadrado após o recorte no pop-up e não depende da extensão GD.

## Editor de mídia — versão 67

O editor de foto é aberto pelo avatar, pelo botão **Alterar foto** ou por teclado. Ele produz um JPEG quadrado com zoom e arraste. O editor de banner produz um JPEG horizontal 3:1 com os mesmos controles. O arquivo `VERSOES-PERFIL.md` cataloga as 67 evoluções do fluxo.

### Evolução 68 do editor

O zoom mínimo do avatar e do banner é 50%, com preenchimento derivado da própria imagem para não revelar áreas vazias. O arraste é limitado às bordas do conteúdo, e as saídas foram ampliadas para 1024×1024 no avatar e 1800×600 no banner, com maior qualidade de interpolação e compressão JPEG.

## Liquid Glass v83 — rodada de 15 evoluções

Todas as 28 páginas visuais do site carregam `css/liquid-glass.css?v=83` e `JS/liquid-glass.js?v=83`. A camada inclui superfícies translúcidas, blur, saturação, borda especular, brilho que acompanha o ponteiro, tilt suave, ripple aquático no clique, estados de foco, tratamento responsivo e fallback para redução de movimento. O catálogo completo está em `VERSOES-LIQUID-GLASS.md`.

## Liquid Glass v98 — segunda rodada de 15 evoluções

A rodada v84-v98 adiciona transições suaves entre páginas, reveal por visibilidade, controles magnéticos, indicação da rota atual, blobs e corrente atmosférica animados, halo de foco, resposta fluida de tabelas e gates para touch e redução de movimento. As 30 mudanças acumuladas estão catalogadas em `VERSOES-LIQUID-GLASS.md`.

## Liquid Glass v99 — acabamento suave e parallax

A revisão v99 uniformiza o material em todas as páginas, reduz a intensidade das luzes brancas, neutraliza fundos conflitantes dos itens da navbar, reduz o tilt, adiciona um parallax atmosférico amortecido e uma pequena bolinha de luz que acompanha o mouse. O orbe é não-interativo e é desativado em touch e `prefers-reduced-motion`.


## Configuração atual do banco

A conexão ativa do projeto usa `127.0.0.1:3307`. Se o XAMPP estiver usando outra porta, altere `DB_PORT` no `.env` e a diretiva correspondente no `my.ini` para que os valores coincidam.


## Patentes e desbloqueio do aprendizado

A tabela `usuarios` agora possui a coluna `patente`. bancos antigos recebem a coluna automaticamente; o fluxo também tenta criar a coluna automaticamente quando o usuário abre o aprendizado, o teste ou o perfil. Ao confirmar **Pular Teste**, o endpoint `back-end/pular_teste.php` grava a patente **Ferro 1**, exibe um popup de conquista e libera os cards de **Videoaulas**, **Exercícios** e **Loja** com links para `videoaulas.php`, `exercicios.php` e `loja.php`. A conclusão normal do diagnóstico grava a patente calculada no mesmo campo.


## Liquid Glass v100 — cards e parallax

A revisão v100 uniformiza o material Liquid Glass nos cards das páginas de videoaulas, exercícios e loja, incluindo módulos, aulas, quiz, itens de loja, saldo e caixas auxiliares. O motor global também reconhece novos cards semanticamente e aplica parallax de rolagem, tilt suave ao mover o cursor, brilho especular contextual, ripple de clique e microparallax em ícones e títulos. A navbar mantém seus itens transparentes para evitar fundos conflitantes. O movimento respeita `prefers-reduced-motion` e é desativado em ponteiros de toque para preservar desempenho.


## Navegação e progressão — revisão v101

A navbar compartilhada foi adicionada às páginas internas que não a exibiam, incluindo configurações, preferências, privacidade, ajuda, calendário, sobre e perfil. A navbar agora busca a foto atual diretamente no banco e usa um fallback de inicial quando necessário. Os retornos das videoaulas e exercícios apontam diretamente para `aprendizado.php`, e a loja ganhou o mesmo retorno explícito.

O perfil exibe a patente em destaque com material Liquid Glass, medalha visual e uma barra de XP que informa o total atual, o percentual da patente e quanto falta para a próxima. O campo `xp` foi adicionado à tabela `usuarios`; bancos antigos recebem a coluna automaticamente. O teste diagnóstico concede XP proporcional à pontuação, enquanto pular o teste mantém o usuário em Ferro 1.


## Ajuste v102 — retornos e cartão de patente

O botão superior das videoaulas foi corrigido para apontar para `aprendizado.php`, em vez de `loja.php`. Os botões educacionais agora exibem apenas “Voltar”. O cartão de patente do perfil foi refinado com a hierarquia do exemplo fornecido: patente atual em destaque, ícone/medalha, linha de XP de treino no formato atual/meta, barra de progresso e indicação de XP restante para subir.

## Sunset Hub integrado — área da comunidade

O portal agora inclui a área **Comunidade** na navbar, usando o login/sessão e o mesmo banco do TCC. Em banco vazio, a aplicação inicializa o esquema completo pelo dump principal; em banco do TCC existente, cria automaticamente apenas as tabelas comunitárias faltantes, sem executar migração separada. O Sunset Hub oferece amizades e mensagens privadas, vídeos MP4 com miniatura/views/reações/comentários, carrosséis, vídeos curtos, stories temporários e grupos com salas administráveis. O guia completo está em [`community/README.md`](community/README.md) e [`README-INTEGRACAO-SUNSETHUB.md`](README-INTEGRACAO-SUNSETHUB.md).

O XAMPP desta máquina está configurado para MySQL em `127.0.0.1:3307`; para arquivos acima do limite padrão do PHP, ajuste `upload_max_filesize` e `post_max_size` em `C:\xampp\php\php.ini` conforme o guia da comunidade e reinicie o Apache. Videochamadas e sincronização com plataformas externas são áreas/integrações não habilitadas.
