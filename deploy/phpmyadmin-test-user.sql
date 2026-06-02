-- cPanel → phpMyAdmin → base cauanb36_bancodechoices → SQL
-- Cria/atualiza utilizador de teste (senha: BancoTeste2026#Local)

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
