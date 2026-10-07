# Integração PTCC + Sunset Hub

O Sunset Hub foi incorporado como uma área da comunidade no portal, sem uma segunda conta ou banco de dados independente.

- Navegação: item **Comunidade** na navbar compartilhada.
- Conta: usa o login e a sessão já existentes em `usuarios`.
- Banco: permanece o configurado pelo TCC (`DB_NAME`, padrão `educacaofinanceira`).
- Módulo: `community/front-end` e `community/back-end`; esquema incluído em `educacaofinanceira.sql`.
- Instalação: em banco totalmente vazio, a conexão carrega o dump principal automaticamente; em banco existente, as tabelas `comunidade_*` faltantes são criadas automaticamente na primeira visita autenticada. Não há arquivo separado para executar.
- Guia completo: `community/README.md`.

O provisionamento é aditivo e idempotente. O dump completo só é aplicado a um banco sem nenhuma tabela; qualquer banco não vazio é preservado. A conta MySQL precisa de privilégios `CREATE` na primeira inicialização automática.

A área oferece busca e solicitação de amizades, mensagens privadas, vídeos MP4 com miniatura obrigatória, contador de views, likes/dislikes e comentários, carrosséis, vídeos curtos, stories de 24 horas e grupos com salas administráveis. O guia `community/README.md` descreve limites de upload e a configuração do PHP/XAMPP. Videochamadas são apenas uma área reservada. Integração autenticada/sincronização direta com Discord, Instagram ou YouTube não foi ativada porque depende de credenciais OAuth/API e permissões que não foram fornecidas.
