# LEIA-ME PARA ARRUMAR TUDO

## Estado depois desta rodada

A comunidade foi integrada à experiência visual e à navegação do portal AFDE: acessível pelo item **Comunidade** da navbar e pela rota `front-end/comunidade.php`; a rota antiga redireciona para ela. A tela usa o tema do usuário, os estilos compartilhados e o sistema Liquid Glass. Também foi acrescentado um menu mobile acessível para a navbar principal.

Este arquivo preserva os problemas fora da comunidade identificados anteriormente e distingue o que foi corrigido do que foi deliberadamente desativado porque não existia um backend seguro.

## Problemas tratados

### 1. Menu principal desaparecia em telas pequenas — corrigido

A navbar tinha `display:none` abaixo de 768 px e não oferecia controle para abrir o menu. Agora há botão com `aria-expanded`, operação por teclado, Escape, clique externo e fechamento ao escolher um link. Testar visualmente em 320 px, 768 px e desktop após instalar no XAMPP.

### 2. Preferências de notificações sem endpoint — desativadas com aviso

O formulário apontava para `back-end/atualizar-notificacoes.php`, inexistente. Para evitar que pareça salvar opções sem persistência, a submissão foi retirada e a janela explica que nenhuma mudança será aplicada. Implementar persistência autenticada/CSRF no banco antes de reativar.

### 3. Exportação e exclusão de conta — desativadas com aviso

As rotas `back-end/exportar-dados.php` e `back-end/excluir-conta.php` não existiam; o script `js/modais.js` também estava ausente. Os botões agora não levam a 404 e a exclusão não pode ser disparada. Para oferecer as funções, implementar exportação isolada por usuário e, para exclusão, fluxo POST autenticado com CSRF, confirmação server-side, política de retenção e limpeza das relações. Não usar GET para excluir dados.

### 4. Ilustrações de renda variável — corrigidas

As imagens PNG dos cards não existiam. Foram adicionadas quatro ilustrações SVG locais em `front-end/img/` e os caminhos da página foram apontados para elas.

### 5. Checkout de planos pagos — indisponível de forma explícita

`front-end/checkout.php` não existe. O formulário que pedia número de cartão, CVV e CPF foi removido. Os botões pagos mostram um aviso e não coletam dados nem enviam cobrança. Para vender planos, integrar um provedor real, com preço validado no servidor, CSRF, sandbox, webhook idempotente e fluxo de cancelamento. Não usar dados de cartão de verdade neste projeto enquanto isso não existir.

## Escopo da comunidade verificado estaticamente

A interface existente contempla busca e pedidos de amizade, mensagens privadas com imagem, vídeos MP4 e miniatura, visualizações/reações/comentários, carrosséis, vídeos curtos, stories de 24 horas, grupos com chat/imagens, avisos, abas administrativas e lista de presença. Cards de vídeo abrem o visualizador e cards de grupo abrem a sala/modal. Chamadas de vídeo continuam sendo somente um espaço reservado.

A mudança de rota foi refletida nas URLs da API e nos caminhos de mídia. A página herda o tema selecionado na conta. A ligação real ao Discord, Instagram e YouTube não foi habilitada: depende de OAuth/API e credenciais externas.

## Limite importante do teste

Foram feitas verificações estáticas de sintaxe PHP/JavaScript e consistência de rotas/markup. **Não foi possível certificar nesta sessão a execução ponta a ponta no navegador nem a leitura/gravação no MySQL do XAMPP**, pois a instância local em `127.0.0.1:3307` e sockets locais estavam indisponíveis/bloqueados no ambiente de execução. Portanto, não declaro que tudo está “100% funcional” sem o reteste local autenticado.

## Roteiro de teste no XAMPP

1. Extraia o projeto mantendo sua estrutura e configure o Apache com a pasta do projeto como raiz.
2. Inicie Apache e MySQL; confirme `DB_HOST=127.0.0.1`, `DB_PORT=3307`, `DB_NAME=educacaofinanceira` e as credenciais locais corretas. Não substitua senhas locais por valores de exemplo.
3. Use banco de teste ou backup antes de testar. O provisionamento da comunidade exige permissão `CREATE`; o dump principal só é importado automaticamente em banco vazio.
4. Entre no portal e acesse **Comunidade** pela navbar; confirme tema claro/escuro, refresh, retorno do login e navegação entre as seções.
5. Teste duas contas de QA: busca/pedido/aceitação de amizade, mensagem de texto/imagem, notificações, criação/participação/administração de grupo, abas, moderação, sala e envio de imagem.
6. Teste vídeo e carrossel com arquivos de teste pequenos. Para uploads maiores, confira `upload_max_filesize`, `post_max_size`, `upload_tmp_dir` e espaço/permissões na pasta `community/uploads` do PHP/Apache.
7. Em celular, valide menu global, navbar, tabs da comunidade, salas/modais e rolagem em 320 px e 768 px.
8. Confirme também que planos pagos, exportação e exclusão ficam claramente indisponíveis — não insira dados financeiros reais.

## Não ativado

- Checkout e processamento de pagamentos.
- Exportação de dados e exclusão da conta.
- Preferências persistentes de notificações do portal.
- Chamadas de vídeo com câmera/microfone.
- Conexões diretas com serviços externos Discord, Instagram e YouTube.


## Auditoria final de segurança e empacotamento

### Correções aplicadas nesta rodada

- A mídia de conversas privadas e de grupo agora passa por `community/back-end/media.php`, que exige sessão válida e confirma no banco a relação de amizade/conversa ou a participação atual no grupo antes de entregar o arquivo. O arquivo é servido como imagem com `nosniff`, política de cache privada e resposta uniforme para arquivo inexistente/não autorizado. O acesso HTTP direto à pasta `community/uploads/chat/` é negado por `.htaccess`.
- O helper de API aplica limites de requisição por sessão: por exemplo, busca de usuários (30/min), polling de mensagens (180/min), notificações (90/min), comentários (60/min) e limites específicos para ações de escrita/upload. Os contadores são saneados antes de uso.
- Uploads da comunidade limitam tamanho, aceitam somente tipos MIME permitidos, verificam imagens, e usam nomes aleatórios gerados no servidor. O handler não aceita métodos diferentes de GET para servir mídia.
- O encerramento de sessão em oito páginas do portal agora usa `encerrar_sessao_segura()`, que limpa os dados da sessão e expira o cookie, em vez de apenas apagar os dados no servidor.
- `.htaccess` na raiz impede listagem e nega acesso web a `.env`, arquivos de configuração selecionados, dumps SQL, backups e logs; uploads também não executam scripts PHP.

### Limites e configurações de implantação

- O rate-limit é **por sessão PHP**, não uma proteção global contra abuso distribuído; em produção, complemente com limites no proxy/web server e monitoramento. Os limites também podem exigir ajuste se polling muito intenso ou rede compartilhada bloquear usuários legítimos.
- As regras `.htaccess` exigem Apache com `mod_rewrite`/`mod_authz_core` e `AllowOverride` habilitado para a pasta do projeto. O XAMPP/Apache é o alvo deste pacote; em Nginx, configure bloqueio equivalente para `community/uploads/chat/` e arquivos sensíveis.
- A CSP permanece deliberadamente parcial (`frame-ancestors`, `object-src`, `base-uri`): há scripts inline no portal, então uma política mais estrita sem refatoração poderia interromper páginas existentes. Mover scripts inline para arquivos externos ou usar nonces é o próximo passo para reduzir esse risco sem quebrar o portal.
- Não foi possível executar teste autenticado ponta a ponta contra o MySQL do XAMPP nesta sessão. A checagem final executada encontrou **78 arquivos PHP sem erro de sintaxe e 9 arquivos JavaScript sem erro de sintaxe**, mas isso não substitui teste com duas contas e banco real.
- O `.env` real não foi incluído no ZIP; somente `.env.example`, com valores de exemplo/placeholders. Crie seu `.env` local a partir dele e nunca publique credenciais reais.
