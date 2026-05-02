# Checklist: o que falta para produção (100%)

**Projeto:** Banco de Choices (Laravel)  
**Auditoria:** análise do repositório (código, config, rotas, `lang/`, `storage/`, testes) — 2026-04-27

Este documento lista **gaps técnicos e operacionais** típicos de “pronto para produção”: segurança, operação, conteúdo, observabilidade e dívida de produto. Não é um compromisso de que tudo é obrigatório — ajuste por risco (RGPD, volume, orçamento).

**Implementações no repositório (2026-04-28):** `.env.example` alinhado ao produto e à produção; `config/trustedproxy.php`; webhook MP com assinatura obrigatória quando `APP_ENV=production` (`MP_REQUIRE_WEBHOOK_SIGNATURE` para sobrepor); middleware `SecurityHeaders`; rate limits em login, cadastro, recuperação de senha, checkout e APIs públicas; logout só por `POST`; CI GitHub Actions (`composer test`); Dependabot; `README` de deploy; exemplo Supervisor; `Dockerfile` + `docker-compose.yml` de referência; testes de `/up` e webhook; `stichoza/google-translate-php` em `require-dev`; `phpunit.xml` com `APP_URL` estável para testes; triagem inicial em `docs/MIGRACAO_PENDENCIAS.md`. Continua **fora do código**: backups e BD em produção, credenciais LIVE Mercado Pago, mail real, workers no servidor, dados/i18n das questões, observabilidade externa (Sentry, etc.).

---

## 1. Resumo executivo

| Área | Estado | Prioridade típica |
|------|--------|-------------------|
| `.env` / chaves / debug | `APP_DEBUG`, `APP_KEY`, `APP_URL` precisam revisão em prod | **Crítica** |
| Base de dados | SQLite no exemplo; em prod usar MySQL/MariaDB/PostgreSQL + backup | **Crítica** |
| Fila de jobs | `QUEUE_CONNECTION=database` implica **worker** permanente | **Alta** |
| Mercado Pago | Credenciais de **produção**, URL pública, segredo de webhook, HTTPS | **Alta** |
| E-mail | `MAIL_*` ainda de exemplo; transacional (registo, acesso) | **Alta** |
| Tradução da **UI** | `pt_BR` / `en_US` / `es_AR` com **666 chaves** e paridade (ver §11) | OK na estrutura |
| Tradução das **questões** | Ficheiros em `storage/app/data/i18n/…` **não estão** no repositório analisado | **Alta** (produto) |
| Testes automatizados | Só `ExampleTest` | **Média–Alta** |
| CI/CD | Sem pipeline (GitHub Actions, etc.) | **Média** |
| Documentação de deploy | `README.md` ainda o **padrão do Laravel** | **Média** |
| Dívida da migração | `docs/MIGRACAO_PENDENCIAS.md` (2026-04-15) — verificar o que ainda aplica (§12) | **Baixa–Média** |

---

## 2. Infraestrutura e deploy

- **Hospedagem:** não há `Dockerfile`, `docker-compose` nem receita de deploy (Forge, PaaS, K8s). Falta descrever **onde** o PHP corre (versão, extensões: `openssl`, `pdo`, `mbstring`, `gd` sugerida para PDF).
- **HTTPS e proxies:** em produção atrás de load balancer, configurar `TrustProxies` e variáveis `APP_URL` com `https://` para links e Mercado Pago.
- **Build de assets:** `composer run setup` referencia `npm run build` — em deploy é preciso `public/build` gerado; o projeto usa sobretudo `public/assets/*` (CSS estático) e **Vite** só em `welcome.blade.php`; confirmar se o build é mesmo necessário no teu deploy.
- **Ficheiros graváveis:** `storage/`, `bootstrap/cache/` com permissões corretas; `php artisan storage:link` se usar ficheiros públicos.
- **Healthcheck:** existe rota `/up` (Laravel 12). Integrar no monitor do hosting.

---

## 3. Variáveis de ambiente (`.env`)

- **`APP_NAME`:** ainda “Laravel” no `.env.example` — personalizar.
- **`APP_DEBUG=false`** e **`APP_ENV=production`** no servidor; nunca expor erros e stack.
- **`APP_KEY`:** obrigatório; gerar e guardar de forma segura.
- **Sessão em HTTPS:** avaliar `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=lax` ou `strict` conforme domínio; `SESSION_ENCRYPT` pode ser ativado se quiseres camada extra.
- **`LOG_LEVEL=error`** (ou `warning`) em produção; hoje o exemplo aponta `debug`.
- **Base de dados:** trocar SQLite por serviço gerido; definir `DB_*` e migrar; planear **backups** e testes de restore.
- **Redis (opcional):** cache, sessão e fila escalam melhor; não está exigido no exemplo.

---

## 4. Fila, agendamento e tarefas em segundo plano

- O `composer` script `dev` arranca `queue:listen` — em produção falta processo **supervisado** (systemd, Supervisor) com `php artisan queue:work` (ou `queue:listen` com cuidado), retries e `failed_jobs`.
- Não há `Schedule::` em `routes/console.php` (só o comando de exemplo `inspire`). Se no futuro houver faturação, lembretes ou limpeza, será preciso `cron` a apontar para `schedule:run`.

---

## 5. Mercado Pago (checkout e webhook)

- **`MP_ACCESS_TOKEN` / `MP_PUBLIC_KEY`:** trocar de `TEST-` para produção; validar `MP_CURRENCY_ID` (ex.: `ARS`) e regras de imposto/PAís.
- **`SITE_URL` / `APP_URL` / `MP_CHECKOUT_BASE_URL`:** alinhar com o domínio real e HTTPS; túneis (ngrok) são só dev.
- **`MP_WEBHOOK_SECRET`:** em produção deve ser **obrigatório** validar assinatura; sem segredo, o webhook aceita tráfego não assinado (código deixa de validar se estiver vazio).
- **URL do webhook:** registrar no painel Mercado Pago: `POST /webhook-mercadopago` acessível publicamente.
- **Idempotência e logs:** o fluxo usa `mp_payment_processed` e serviços; monitorizar falhas no `Log` e reconciliação manual se necessário.

---

## 6. E-mail

- **`MAIL_MAILER=log`** no exemplo — e-mails reais (boas-vindas, acesso) não saem. Configurar SMTP, SES, Mailgun, etc.
- Ajustar **`MAIL_FROM_ADDRESS`** e nome da marca; testar entrega e spam (SPF, DKIM no DNS).

---

## 7. Segurança da aplicação

- **Rate limiting:** rotas sensíveis (`POST /login`, `POST /cadastro`, `POST /process-payment-mp`, `POST /forgot` se existir) deveriam usar `throttle` (ou middleware de login Laravel) para reduzir brute force e abuso.
- **APIs públicas** (`routes/api.php` — geolocalização, CEP, ViaCEP): estão **sem** autenticação; qualquer origem pode consumir. Para produção, considerar **throttle**, token interno, ou CORS restrito, conforme o risco.
- **Logout via GET** (`Route::match(['get', 'post'], '/logout', …)`): prático para links antigos mas **vulnerável a CSRF/tracking**; preferir `POST` só, ou proteger o GET com medidas adicionais.
- **Cabeçalhos de segurança:** CSP, HSTS, `X-Frame-Options` — hoje dependem do servidor web; pode usar middleware Laravel.
- **Dependências:** `composer audit` / GitHub Dependabot; atualizar `laravel/framework` e SDK Mercado Pago com regularidade.
- **Contas de teste:** `config/test_users.php` + `TEST_USER_SKIP_DEFAULT_MATERIAS` — garantir que e-mails de teste **não** vão parar a produção acidentalmente.

---

## 8. Testes e qualidade

- **Cobertura mínima inexistente** para o negócio: auth, checkout, webhook (assinatura e processamento), simulação, `HistoricoSimulado`, PDF de estatísticas.
- `phpunit.xml` configura `sqlite :memory:` para testes — alinhar com `RefreshDatabase` e factories onde fizer sentido.
- Não há **lint/CI** automático (Pint/PHPUnit) em pull requests.

---

## 9. Observabilidade e suporte

- **Logs:** `storage/logs` local; em produção, enviar para agregador (Sentry, Datadog, CloudWatch, etc.) — hoje **não** há integração.
- **Métricas e alertas:** fila atrasada, taxa 5xx, falhas de pagamento, webhook 401/500.
- **Rota de saúde** `/up` — usar em uptime monitoring.

---

## 10. Dados, questões e conteúdo

- Ficheiros grandes em `storage/app/data/questoes_*.json` — **incluir no backup** e (se forem propriedade da equipa) em **repositório ou artefacto** versionado; tratar como dado de produto.
- **`QuestionLocale`:** aplica tradução quando existem ficheiros em `storage/app/data/i18n/{pt_BR,en_US}/<mesmo_nome-do-banco>.json`. Sem esses ficheiros, a UI fica no idioma escolhido **mas o texto das questões continua o do JSON base (espanhol)**, em linha com a referência. Falta: **gerar, rever e publicar** esses JSON (comando `php artisan questions:build-i18n` existe, depende de rede/Tradução e revisão humana).
- **Qualidade editorial:** revisão contínua (typos, opções truncadas, feedbacks em idioma misto) — fazer varredura periódica; o comando `questions:analyse` ajuda a estatísticas.
- **Histórico de resultados** (`detalhes_json` em `HistoricoSimulado`): o texto fica **congelado** no momento do resultado; mudar de idioma depois não re-traduz automaticamente. Para paridade com o locale atual, seria necessário recompor a partir de `numero` + banco (evolução futura).

- **`stichoza/google-translate-php`:** útil em desenvolvimento/geração de patches; em produção **não** deveria ser dependência de runtime se não for usada na aplicação web; avaliar `require-dev` ou remover do caminho de pedido.

---

## 11. Internacionalização (UI)

- Os três ficheiros `lang/*.json` têm **666 chaves** e **paridade** entre `pt_BR`, `en_US` e `es_AR` (verificação feita a 2026-04-27).
- **Default da app no `.env.example`:** `APP_LOCALE=en` enquanto o produto (PROJETO.md) fala em `es_AR` por sessão — alinhar default com a estratégia de região/idioma de entrada.

---

## 12. Dívida documentada (migração antiga)

O ficheiro `docs/MIGRACAO_PENDENCIAS.md` lista problemas de migração PHP → Laravel (views incompletas, chaves, fluxo addon, etc.). **Muita coisa já evoluiu no código** (ex.: rotas e `AddonController` existem; chaves `result.*` em `pt_BR.json`). Recomendação: **fazer triagem** desse ficheiro e fechar/actualizar itens, para não desorientar a equipa. O que ainda faltar é sobretudo **fidelidade de UX/conteúdo** vs. o PHP antigo, não só “produção técnica”.

---

## 13. Repositório e higiene

- Não existe `.github/workflows`.
- Pode existir rasto de **worktrees** (ex.: pastas `.claude/worktrees/...`) e ficheiros não rastreados; limpar o que não deve ir para o deploy ou para o Git.

---

## 14. Checklist rápida antes do go-live

- [ ] `APP_DEBUG=false`, `APP_ENV=production`, `APP_KEY` e `APP_URL` (https) corretos
- [ ] Base de dados de produção migrada, com backups agendados e teste de restore
- [ ] `php artisan config:cache`, `route:cache`, `view:cache` no deploy (e `config:clear` quando alterares `.env`)
- [ ] Worker de fila a correr; `failed_jobs` monitorizado
- [ ] Mercado Pago: credenciais live, webhook HTTPS com segredo, `POST /webhook-mercadopago` acessível
- [ ] E-mail transaccional a funcionar (SPF/DKIM)
- [ ] Ficheiros de questões e `storage/` incluídos no plano de backup
- [ ] (Opcional produto) JSON de questões em `i18n/` para `pt_BR` e `en_US` completos e revistos
- [ ] Testes automáticos mínimos nos fluxos críticos; CI a correr
- [ ] `README` do projeto a substituir o texto genérico do Laravel com: requisitos, deploy, variáveis e comandos
- [ ] Revisar `MIGRACAO_PENDENCIAS.md` e arquivar o que estiver concluído

---

*Última revisão: 2026-04-27. Atualizar este documento quando fechares itens ou mudares a arquitectura.*
