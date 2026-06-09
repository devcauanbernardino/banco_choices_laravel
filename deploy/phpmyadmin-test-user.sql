-- cPanel → phpMyAdmin → base cauanb36_bancodechoices → SQL
-- Cria/atualiza utilizador de teste (senha: BancoTeste2026#Local)
-- Acesso: matérias 1–5 (todas com banco de questões no catálogo)

INSERT INTO `users` (`nome`, `email`, `senha`, `created_at`)
VALUES (
  'Usuário Teste',
  'teste@bancodechoices.com',
  '$2y$10$GNk71PuxLUwdid7Alp/97u8iJxcBH.cIhFdbaupF5rrFqmdFcsqoG',
  NOW()
)
ON DUPLICATE KEY UPDATE
  `nome` = VALUES(`nome`),
  `senha` = VALUES(`senha`);

INSERT IGNORE INTO `usuarios_materias` (`usuario_id`, `materia_id`)
SELECT u.id, m.id
FROM `users` u
CROSS JOIN (
  SELECT 1 AS id UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
) m
WHERE u.email IN ('teste@bancodechoices.com', 'teste@bancodechoices.local');
