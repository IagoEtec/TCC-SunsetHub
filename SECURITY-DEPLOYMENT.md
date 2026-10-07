# Checklist de implantação segura

1. Sirva somente HTTPS e mantenha `REQUIRE_HTTPS=1`.
2. Coloque o `.env` fora da raiz pública; o `.htaccess` é uma defesa adicional, não substitui isso.
3. Use um usuário MySQL dedicado (`afde_app`), sem privilégios de `DROP`, `ALTER` ou `GRANT` em produção; rode migrações em pipeline separado.
4. O dump principal já inclui os índices de segurança; valide duplicidades existentes antes de importar dados antigos.
5. Mantenha portas 3000/5000 ligadas apenas em loopback ou atrás de proxy autenticado.
6. Configure `PLUGGY_WEBHOOK_TOKEN`, `ASAAS_WEBHOOK_TOKEN` e chaves reais apenas no ambiente de execução.
7. Instale GD para que uploads sejam re-encodados como JPEG.
8. Faça backup criptografado, rotação de segredos e auditoria de logs.
9. Rode `php -l`, `composer audit` e testes de integração em cada deploy.

10. O dump único já cria as tabelas Arena prefixadas e vincula cada progresso ao `usuarios.id`, sem usuário global compartilhado.

11. O XAMPP usado neste projeto escuta MySQL/MariaDB em `3307`; configure `DB_PORT=3307` localmente. Em outro ambiente, faça `DB_PORT` coincidir com a porta real do servidor.
