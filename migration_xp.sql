-- Adiciona XP persistido ao perfil para exibir a progressão de patente.
-- Execute uma vez em bancos já existentes.
ALTER TABLE usuarios ADD COLUMN xp INT NOT NULL DEFAULT 0 AFTER patente;
