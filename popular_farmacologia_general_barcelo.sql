-- Script para popular questoes da materia Farmacología General (Barceló)
-- Faculdade: Barceló, agrupamento_id = 5 (2º Año Barceló)
-- Rodar inteiro de uma vez no phpMyAdmin (aba SQL) no banco de producao.
-- O arquivo storage/app/data/questoes_farmacologia_general_barcelo.json deve ser copiado antes.

INSERT IGNORE INTO materias (nome, slug, agrupamento_id, ordem)
VALUES ('Farmacología General', 'farmacologia-general-barcelo', 5, 10);

SET @materia_id = (SELECT id FROM materias WHERE slug = 'farmacologia-general-barcelo' LIMIT 1);

INSERT INTO questoes (materia_id, catedra_id, overlay_key, parcial, tema, is_demo, created_at, updated_at)
SELECT @materia_id, NULL, v.overlay_key, NULL, v.tema, (v.overlay_key < 12), NOW(), NOW()
FROM (
  SELECT 0 AS overlay_key, 'Tema 1' AS tema
  UNION ALL SELECT 1, 'Tema 1'
  UNION ALL SELECT 2, 'Tema 1'
  UNION ALL SELECT 3, 'Tema 1'
  UNION ALL SELECT 4, 'Tema 1'
  UNION ALL SELECT 5, 'Tema 1'
  UNION ALL SELECT 6, 'Tema 1'
  UNION ALL SELECT 7, 'Tema 1'
  UNION ALL SELECT 8, 'Tema 1'
  UNION ALL SELECT 9, 'Tema 1'
  UNION ALL SELECT 10, 'Tema 1'
  UNION ALL SELECT 11, 'Tema 1'
  UNION ALL SELECT 12, 'Tema 1'
  UNION ALL SELECT 13, 'Tema 1'
  UNION ALL SELECT 14, 'Tema 1'
  UNION ALL SELECT 15, 'Tema 1'
  UNION ALL SELECT 16, 'Tema 1'
  UNION ALL SELECT 17, 'Tema 1'
  UNION ALL SELECT 18, 'Tema 1'
  UNION ALL SELECT 19, 'Tema 1'
  UNION ALL SELECT 20, 'Tema 2'
  UNION ALL SELECT 21, 'Tema 2'
  UNION ALL SELECT 22, 'Tema 2'
  UNION ALL SELECT 23, 'Tema 2'
  UNION ALL SELECT 24, 'Tema 2'
  UNION ALL SELECT 25, 'Tema 2'
  UNION ALL SELECT 26, 'Tema 2'
  UNION ALL SELECT 27, 'Tema 2'
  UNION ALL SELECT 28, 'Tema 2'
  UNION ALL SELECT 29, 'Tema 2'
  UNION ALL SELECT 30, 'Tema 2'
  UNION ALL SELECT 31, 'Tema 2'
  UNION ALL SELECT 32, 'Tema 2'
  UNION ALL SELECT 33, 'Tema 2'
  UNION ALL SELECT 34, 'Tema 2'
  UNION ALL SELECT 35, 'Tema 2'
  UNION ALL SELECT 36, 'Tema 2'
  UNION ALL SELECT 37, 'Tema 2'
  UNION ALL SELECT 38, 'Tema 2'
  UNION ALL SELECT 39, 'Tema 2'
) AS v;

-- Vincular temas aos parciais para filtragem hierárquica
UPDATE questoes SET parcial = '1' WHERE materia_id = @materia_id AND tema = 'Tema 1';
UPDATE questoes SET parcial = '2' WHERE materia_id = @materia_id AND tema = 'Tema 2';
