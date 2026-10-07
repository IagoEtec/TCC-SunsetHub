-- Hardening de integridade; execute uma vez em banco de produção.
ALTER TABLE usuarios ADD UNIQUE KEY uq_usuarios_email (email);
ALTER TABLE usuarios ADD UNIQUE KEY uq_usuarios_oauth_uid (oauth_uid);
ALTER TABLE usuarios MODIFY email VARCHAR(190) NOT NULL;
