# Integração PTCC + AFDIS Arena

A área `front-end/aprendizado.php` agora usa a interface Liquid Glass do AFDis Arena, mas o acesso, a sessão e a identidade vêm do PTCC. O backend foi refeito em `back-end/aprendizado_api.php` para que XP, moedas, progresso, comentários, votos, streak e itens sejam separados por usuário autenticado.

## Instalação

1. Crie/selecione o banco `educacaofinanceira`.
2. Importe somente `educacaofinanceira.sql`; ele já contém PTCC, Open Finance, Arena e todo o conteúdo financeiro. Ela cria somente tabelas com prefixo `arena_`, vincula `arena_users.user_id` à tabela `usuarios` e insere a trilha de aulas/exercícios.
3. Acesse `front-end/aprendizado.php` já autenticado no PTCC.

A migração foi feita para ser repetível: módulos, aulas e exercícios possuem chaves de unicidade apropriadas. O banco independente `afdis.sqlite` do projeto Arena não é usado pela integração, evitando conta global compartilhada e mistura de dados.

## Segurança aplicada

A API exige sessão autenticada e CSRF em todas as operações mutáveis. O navegador recebe apenas perguntas e alternativas; as respostas corretas permanecem no servidor. IDs de aula, comentário, voto e item são validados novamente no backend, compras usam atualização condicional de saldo, recompensas e progresso são transacionais, e o reset apaga apenas os dados do usuário atual. Textos de comentários são escapados no frontend com `textContent`/função de escape antes de `innerHTML`, e o estado inicial usa flags JSON hexadecimal para não permitir quebra do `<script>` por nome de usuário malicioso.

## Compatibilidade

A integração usa a conexão MySQL existente do PTCC (`DB_HOST`, `DB_PORT=3307` no XAMPP deste projeto, `DB_NAME`, `DB_USER`, `DB_PASS`) e o mecanismo de sessão/CSRF já endurecido. O `navbar.php` continua presente, portanto os demais fluxos da plataforma não são substituídos.
