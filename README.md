# Banco de Choices (Laravel)

Plataforma web de simulados e banco de questões com checkout **Mercado Pago**, painel autenticado, histórico e estatísticas.

Documentação funcional e de arquitetura: [`docs/PROJETO.md`](docs/PROJETO.md). Checklist de produção: [`docs/PRODUCAO_PENDENCIAS.md`](docs/PRODUCAO_PENDENCIAS.md).

## Requisitos

- PHP **8.2+** com extensões habituais Laravel: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`
- **Composer** 2.x e **Node.js** 18+ (para build Vite onde aplicável)
- `ext-gd` recomendada para exportação PDF com imagens (Dompdf)

## Arranque rápido (desenvolvimento)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build   # opcional se usar assets Vite
php artisan serve
```

Na primeira execução pode usar `composer run setup` (instala dependências, `.env`, migrações e build).

### Fila em segundo plano

Com `QUEUE_CONNECTION=database`, é necessário um worker em desenvolvimento **e** em produção:

```bash
php artisan queue:work --tries=3
```

Em produção use **Supervisor**, **systemd** ou equivalente — exemplo em [`docs/deploy/supervisor-queue.conf.example`](docs/deploy/supervisor-queue.conf.example).

### Cron

Configure no servidor `* * * * * php artisan schedule:run` (quando existirem tarefas em `Schedule`; ver comentários em `routes/console.php`).

## Variáveis de ambiente importantes

| Variável | Notas |
|----------|--------|
| `APP_ENV`, `APP_DEBUG`, `APP_URL` | Em produção: `production`, `false`, URL pública `https://…` |
| `APP_KEY` | `php artisan key:generate` |
| `DB_*` | Em produção usar MySQL/MariaDB/PostgreSQL com backups |
| `MP_*` | Credenciais Mercado Pago; em produção `MP_WEBHOOK_SECRET` obrigatório (assinatura do webhook) |
| `MAIL_*` | SMTP ou serviço transacional (não usar só `log` em produção) |
| `TRUSTED_PROXIES` | Atrás de load balancer: típico `*` ou IPs dos proxies |
| `QUEUE_CONNECTION` | `database` ou `redis`; garantir worker |

Copie `.env.example` e ajuste comentários por ambiente.

## Deploy (resumo)

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build` se o pipeline usar Vite (`public/build`)
3. `php artisan migrate --force`
4. `php artisan storage:link` se servir ficheiros públicos em `storage/app/public`
5. `php artisan config:cache`, `route:cache`, `view:cache`
6. Permissões em `storage/` e `bootstrap/cache/`
7. Worker da fila + agendamento `schedule:run`
8. HTTPS + webhook Mercado Pago apontando para `POST /webhook-mercadopago`

Imagens Docker opcionais: `Dockerfile` e `docker-compose.yml` na raiz (referência mínima).

## Testes e qualidade

```bash
composer test
./vendor/bin/pint
```

CI sugerido: GitHub Actions em `.github/workflows/ci.yml`.

## Licença

MIT (herança do skeleton Laravel; ajuste conforme o teu projeto).
