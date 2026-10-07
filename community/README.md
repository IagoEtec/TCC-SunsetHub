# Sunset Hub integrado ao PTCC

O Sunset Hub é uma área autenticada do portal de Educação Financeira. O portal continua sendo a única fonte de identidade, sessão e banco de dados; não há contas comunitárias separadas.

## Organização

- `front-end/`: tela e assets do Sunset Hub.
- `back-end/`: API, integração de sessão/CSRF e criação idempotente das tabelas sociais.
- `uploads/`: arquivos de vídeo, miniaturas, grupos, stories, publicações visuais e imagens de chat; as subpastas são criadas no primeiro upload.
- `database/`: documentação do esquema, incluído no dump principal `educacaofinanceira.sql`.

## Instalação no XAMPP (banco único)

1. Inicie Apache e MySQL/MariaDB no XAMPP. Este projeto está configurado para o MySQL em `127.0.0.1:3307`.
2. Confirme em `.env` (ou crie a partir de `.env.example`) `DB_HOST=127.0.0.1`, `DB_PORT=3307`, `DB_NAME=educacaofinanceira` e as credenciais que funcionam no seu XAMPP.
3. Em uma instalação nova, crie o banco vazio `educacaofinanceira`. Na primeira conexão, o portal importa automaticamente o dump principal, que contém tanto o TCC como o Sunset Hub.
4. Em um banco do TCC que já tenha a tabela `usuarios`, abra a comunidade autenticado: o esquema comunitário faltante é criado no próprio banco. **Não é necessário importar uma migração Sunset separada.**
5. O usuário MySQL precisa de permissão `CREATE` para a inicialização. Bancos com tabelas não são apagados nem substituídos; se a tabela `usuarios` estiver ausente em um banco não vazio, a aplicação interrompe a inicialização em vez de sobrescrever dados.
6. Faça login pelo portal e escolha **Comunidade** na navbar. Visitantes e sessões vencidas são encaminhados ao login central e retornam à comunidade depois de entrar.

O dump principal `educacaofinanceira.sql` contém as 16 tabelas sociais e o bootstrap também cria tabelas/colunas faltantes de modo idempotente para bancos existentes. A conexão padrão usa a porta **3307**.

## Recursos

### Amigos e mensagens

- Busca de usuários pelo nome, envio/cancelamento de solicitações e aceite/recusa.
- Mensagens privadas no estilo chat, somente entre amizades aceitas; atualização automática e envio de imagens JPG/PNG/WebP até 8 MB.
- Lista de solicitações recebidas, amigos online e últimas mensagens.

### Vídeos, posts visuais e stories

- Vídeos MP4 enviados ao próprio servidor, com título, descrição e miniatura obrigatória.
- Contagem de visualizações no primeiro play por carregamento da página, likes, dislikes e comentários.
- Ao abrir um card de vídeo, um pop-up reúne o player, o autor, as visualizações, as reações e os comentários.
- O autor do vídeo ou do comentário pode apagar comentários; o backend valida a permissão.
- Publicações visuais em carrossel de 1–10 imagens e vídeos curtos MP4.
- Stories de imagem ou vídeo, visíveis por 24 horas e removidos da listagem após a expiração.

### Grupos

- Separação entre **Meus grupos** e **Grupos disponíveis**.
- Criação com nome, descrição e imagem obrigatória; o criador torna-se administrador e primeiro membro.
- Cada grupo abre em pop-up; o bate-papo aceita imagens JPG/PNG/WebP até 8 MB, além de texto.
- Bate-papo, avisos e espaço de videochamada reservado (a chamada em si ainda não está habilitada).
- Lista de membros online/offline; presença considera atividade recente, com verificação de cinco minutos.
- Administrador identificado por coroa, pode criar e excluir abas de chat, avisos ou chamadas e moderar mensagens do grupo.

### Notificações

- O sino consulta novas solicitações de amizade, aceitações, entradas em grupos criados pelo usuário e publicações de amigos (posts, vídeos, carrosséis/vídeos curtos e stories).
- Notificações novas são atualizadas periodicamente enquanto a página estiver aberta; abrir o sino mostra a lista e permite marcar itens como lidos.

### Navegação

- Tour introdutório em quatro passos, exibido uma vez por conta/navegador.
- Busca local na comunidade, navegação com histórico do navegador e layout responsivo.
- Fluxos de publicação mostram erros de upload de forma clara, incluindo limites do PHP.

## Configuração de uploads grandes

O XAMPP desta máquina atualmente informa `upload_max_filesize=40M` e `post_max_size=40M`. O Sunset Hub aceita até **100 MB por MP4** e até **8 MB por imagem**, portanto vídeos/carrosséis grandes exigem um limite global maior que o padrão. Se precisar enviar arquivos acima do limite atual:

1. Abra `C:\xampp\php\php.ini`.
2. Ajuste, por exemplo:
   ```ini
   upload_max_filesize=100M
   post_max_size=115M
   max_input_time=300
   max_execution_time=300
   ```
3. Salve e reinicie o Apache no painel do XAMPP.
4. Confira se a pasta `community/uploads` (e as subpastas criadas automaticamente) pode ser gravada pelo Apache.

O PHP precisa das extensões `pdo_mysql` e `fileinfo`. A pasta de uploads desativa listagem de diretório e bloqueia execução de scripts. Arquivos não são gravados como blobs no MySQL: o banco guarda apenas metadados e caminhos relativos.

## Limites e diagnóstico

- Se a comunidade não abrir, confirme que o MySQL está **iniciado** na porta 3307, o `DB_NAME` aponta para `educacaofinanceira` e a conta tem permissão `CREATE`.
- Se um banco recém-criado estiver vazio, o dump principal é inicializado automaticamente. Não use outro banco isolado ou outro nome de usuário: a FK social referencia `usuarios.id` do TCC.
- Se o login retornar à página inicial em vez da comunidade, use o formulário central e confirme que a URL de entrada tem `?next=community`.
- Se os arquivos grandes forem recusados antes de chegar à API, confira `post_max_size` e `upload_max_filesize`; o Sunset Hub informa esse caso sem expor detalhes internos.
- Chamadas de vídeo são apenas uma área reservada; nenhum dispositivo de câmera/microfone ou serviço externo é conectado.
- O módulo não sincroniza diretamente com Discord, Instagram ou YouTube: OAuth e permissões desses serviços não estão configurados.
