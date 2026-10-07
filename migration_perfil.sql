-- Execute uma vez no banco educacaofinanceira se ele já existia antes do recurso de banner.
ALTER TABLE usuarios ADD COLUMN banner VARCHAR(255) NULL AFTER foto;
