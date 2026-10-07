# Esquema do Sunset Hub

As tabelas `comunidade_*` já estão incluídas no dump principal `educacaofinanceira.sql`. Se o banco estiver totalmente vazio, a conexão principal aplica esse dump automaticamente na primeira requisição.

Se o banco do TCC já estiver em uso e ainda não tiver essas tabelas, o bootstrap autenticado do Sunset Hub as cria automaticamente no mesmo banco, de forma aditiva e idempotente. Bancos não vazios não são sobrescritos. Não é necessário importar ou criar um arquivo de migração separado. A conta MySQL configurada em `.env` precisa ter privilégio `CREATE` para a primeira inicialização.
