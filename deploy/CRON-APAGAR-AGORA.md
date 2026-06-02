# Crons na HostGator — o que apagar AGORA

Tens **6 crons a cada minuto**. Isso impede login e favicon de funcionar.

## Apagar estes 5 (botão Excluir em cada um)

| # | Motivo |
|---|--------|
| `config:cache` → cache2.log | Recria cache com valores errados **a cada minuto** |
| `config:cache` → fix.log | Idem (duplicado) |
| `CatalogoSeeder` → seed.log | Só precisas 1×, não a cada minuto |
| `git pull` + sync → deploy.log | Pode falhar e conflitar com outros |
| `git reset` + TestUserSeeder → fix-all.log | **Apaga** depois de 2 min (ou agora se já correu) |

## Podes manter (opcional)

| Cron | Ajuste |
|------|--------|
| `queue:work` com **ea-php82** | Muda para **ea-php83** ou apaga se `QUEUE_CONNECTION=sync` no `.env` |

## Cron único (1×, daqui a 2 min, depois apaga)

```text
cd /home2/cauanb36/repositories/banco_choices_laravel && /usr/local/bin/git fetch origin main && /usr/local/bin/git reset --hard origin/main && /usr/local/bin/ea-php83 artisan config:clear && /usr/local/bin/ea-php83 artisan route:clear && /usr/local/bin/ea-php83 artisan view:clear && /usr/local/bin/ea-php83 artisan db:seed --class=TestUserSeeder --force >> /home2/cauanb36/ultimo-fix.log 2>&1
```

**Não uses `config:cache` em produção** até o site estar estável.

## Login após o cron

- E-mail: `teste@bancodechoices.com` (ou `teste@bancodechoices.local`)
- Senha: `BancoTeste2026#Local`

### Não entra?

| O que vês | Causa | O que fazer |
|-----------|--------|-------------|
| **“E-mail ou senha incorretos”** | Utilizador não existe na BD deste `.env` | Cron `bancodechoices:ensure-test-user` **ou** SQL em [`phpmyadmin-test-user.sql`](phpmyadmin-test-user.sql) |
| **Volta ao login sem mensagem** | Sessão/cookie | `.env`: `SESSION_DRIVER=file`; apaga `bootstrap/cache/config.php`; `config:clear` |
| **Erro 500** | BD/catálogo | Cron `CatalogoSeeder` uma vez |

Abre o site sempre em **https://bancodechoices.com** (mesmo domínio do `APP_URL`, sem `www` diferente).

---

## Demo sem questões (“Esta faculdade ainda não tem questões demo carregadas”)

Precisas de **duas coisas** no servidor:

1. **Ficheiros JSON** em `repositories/banco_choices_laravel/storage/app/data/`  
   (ex.: `questoes_microbiologia_refinado.json` — envia o `data.zip` e extrai aqui)

2. **Cron uma vez** (ou Deploy):

```text
cd /home2/cauanb36/repositories/banco_choices_laravel && /usr/local/bin/ea-php83 artisan db:seed --class=CatalogoSeeder --force && /usr/local/bin/ea-php83 artisan bancodechoices:setup-demo-questions >> /home2/cauanb36/demo-questoes.log 2>&1
```

Lê `demo-questoes.log` — deve listar `uba: N demo`. Depois abre `/probar-gratis/configurar?faculdade=uba`.

## .env no servidor (confere no File Manager)

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
APP_URL=https://bancodechoices.com
```

Depois do cron acima, apaga `bootstrap/cache/config.php` se ainda existir.
