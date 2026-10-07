-- Adiciona a patente persistida ao perfil dos usuários.
-- Execute uma vez em bancos já existentes.
ALTER TABLE usuarios ADD COLUMN patente VARCHAR(30) NULL AFTER banner;
