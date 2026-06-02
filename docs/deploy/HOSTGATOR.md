# Deploy na HostGator — bancodechoices.com

## Pacote gerado no PC

```powershell
.\scripts\build-hostgator-deploy.ps1
```

Gera:
- `deploy-package/` — pasta para upload
- `deploy-bancodechoices.zip` — ZIP para o Gerenciador de arquivos

Inclui `vendor/`, `storage/app/data/` (questões + i18n) e `.env` base (`deploy/.env.hostgator`).

**Antes do upload:** edita `deploy/.env.hostgator` com credenciais MySQL e senha do e-mail do cPanel, volta a correr o script.

---

## 1. cPanel — preparar

| Item | Ação |
|------|------|
| PHP | 8.2+, extensões: pdo_mysql, mbstring, openssl, tokenizer, xml, gd |
| MySQL | Criar BD + utilizador; anotar nome, user, senha |
| SSL | Let's Encrypt para `bancodechoices.com` |
| E-mail | `contato@bancodechoices.com` — usar `mail.bancodechoices.com` no SMTP |

---

## 2. Upload

### Opção A — SSH (recomendado)

```bash
cd ~
# Enviar ZIP (FileZilla ou scp), depois:
unzip deploy-bancodechoices.zip -d banco_choices_laravel
cd banco_choices_laravel
nano .env   # DB_* e MAIL_PASSWORD
bash deploy/hostgator-post-install.sh
```

### Opção B — Gerenciador de arquivos

1. Upload de `deploy-bancodechoices.zip` para `~/`
2. Extrair
3. Editar `.env` (DB e mail)
4. Terminal do cPanel ou SSH: comandos do `hostgator-post-install.sh`

---

## 3. Document root (importante)

O domínio **não** pode apontar para a raiz do Laravel.

**Domínios** → `bancodechoices.com` → **Raiz do documento**:

```text
/home/SEU_USUARIO/banco_choices_laravel/public
```

(ajusta `SEU_USUARIO` — vês em File Manager)

Se não puderes alterar: coloca o conteúdo de `public/` em `public_html/bancodechoices.com/` e o resto do projeto **fora** de `public_html` (menos ideal; pede ajuda se precisares deste layout).

### Plano B (repositório em `repositories/` + domínio em `bancodechoices.com/`)

Quando a HostGator **bloqueia** o document root dentro de `repositories/`:

1. Código Laravel: `/home/SEU_USUARIO/repositories/banco_choices_laravel`
2. Ficheiros web: `/home/SEU_USUARIO/bancodechoices.com/` (cópia de `public/`)
3. `bancodechoices.com/index.php` — usar o modelo em [`deploy/bancodechoices.index.php`](../../deploy/bancodechoices.index.php) (inclui `$app->usePublicPath(__DIR__);`)

**Favicon:** o ícone é servido por `GET /favicon.ico` (Laravel), com fallback para `repositories/.../public/img/`. Apaga **`bancodechoices.com/favicon.ico`** se existir (ficheiro antigo PNG renomeado bloqueia a rota).

**CSS / imagens quebrados:** o `git pull` atualiza o repo, mas **não** copia sozinho `public/img` nem `public/assets` para `bancodechoices.com/`. Depois de cada deploy com ficheiros estáticos novos:

```bash
cd ~/repositories/banco_choices_laravel
git pull
bash deploy/sync-public-docroot.sh
php artisan view:clear
```

---

## 4. Cron (fila)

**Cron Jobs** → cada minuto:

```text
* * * * * cd /home/SEU_USUARIO/banco_choices_laravel && /usr/local/bin/php artisan queue:work database --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

(Ajusta caminho e `php` — no cPanel usa “PHP path” indicado no cron.)

---

## 5. Mercado Pago

- Webhook: `https://bancodechoices.com/webhook-mercadopago`
- Evento: Pagamentos
- `MP_WEBHOOK_SECRET` igual ao `.env`

---

## 6. Testes pós-deploy

1. `https://bancodechoices.com/up` → 200
2. Landing + login
3. Checkout (valor baixo)
4. `storage/logs/laravel.log` sem `signature_invalid`

---

## 7. Atualizar depois

**Push no GitHub não faz deploy automático** neste projeto (o CI só corre testes). Escolhe um fluxo:

| Fluxo | O que fazer |
|--------|-------------|
| **Git no servidor** | `git pull` em `repositories/banco_choices_laravel` + `bash deploy/sync-public-docroot.sh` (Plano B) + `php artisan migrate --force` + `view:clear` |
| **ZIP manual** | No PC: `.\scripts\build-hostgator-deploy.ps1`, upload, extrair |

Comandos típicos no servidor após atualizar código:

```bash
cd ~/repositories/banco_choices_laravel
php artisan migrate --force
php artisan view:clear
# Plano B: sincronizar img/assets/favicon para bancodechoices.com
bash deploy/sync-public-docroot.sh
```

---

## 8. Sem Terminal do cPanel (só Cron + File Manager)

Na HostGator partilhada muitas vezes **não há Terminal**. Usa **Cron Jobs** (Avançado → Cron Jobs) com PHP 8.3 (`ea-php83`).

### A) Criar utilizador de teste (login)

Cron **uma vez** (ex.: daqui a 2 minutos), depois **apaga** o cron:

```text
cd /home2/cauanb36/repositories/banco_choices_laravel && /usr/local/bin/ea-php83 artisan db:seed --class=TestUserSeeder --force >> /home2/cauanb36/seed-teste.log 2>&1
```

Abre `seed-teste.log` no File Manager — deve aparecer algo como `Database seeding completed successfully`.

Login:

- E-mail: `teste@bancodechoices.local`
- Senha: `BancoTeste2026#Local`
- URL: `https://bancodechoices.com/login`

### B) Atualizar código do GitHub (sem terminal)

**Opção 1 — Git™ Version Control (cPanel)**  
Menu **Git™ Version Control** → repositório em `repositories/banco_choices_laravel`.

| Botão | O que faz |
|--------|-----------|
| **Update from Remote** | Só `git pull`. Se o HEAD já for o último commit do GitHub, **não parece mudar nada** — é normal. |
| **Deploy HEAD Commit** | Corre o `.cpanel.yml` (migrate, seed teste, limpar cache, copiar `img/`/`assets/` para `bancodechoices.com/`). Só fica ativo com `.cpanel.yml` no repo e **sem alterações locais não commitadas** no servidor. |

Fluxo recomendado: **Update from Remote** → **Deploy HEAD Commit**.

Remote: `https://github.com/devcauanbernardino/banco_choices_laravel.git` — branch `main`.

Se aparecer *"The system cannot deploy"* / *"uncommitted changes"*:

1. **Não uses só "Update from Remote"** — isso **não** cria o utilizador de teste nem corrige o favicon.
2. Cron **uma vez** (copia a linha `0)` de [`deploy/cron-hostgator-once.txt`](../../deploy/cron-hostgator-once.txt)) ou no File Manager apaga ficheiros alterados dentro do clone (ex. `bootstrap/cache/config.php` se existir e não for o `.env`).
3. Volta ao Git → **Deploy HEAD Commit** (deve ficar azul).

O `.env` no servidor **não** deve estar dentro do clone editado pelo Git; mantém-o só em `repositories/.../.env`.

**Opção 2 — Cron com git pull** (se o Git existir no servidor; testa 1×):

```text
cd /home2/cauanb36/repositories/banco_choices_laravel && /usr/local/bin/git pull origin main >> /home2/cauanb36/git-pull.log 2>&1
```

Lê `git-pull.log`. Se der erro de autenticação, o repo é privado — usa a Opção 1 ou ZIP.

**Opção 3 — ZIP no PC**  
`git pull` local → `.\scripts\build-hostgator-deploy.ps1` → upload ZIP → extrair por cima de `repositories/banco_choices_laravel` (não apagues `.env`).

### C) Limpar cache após atualizar

Cron **uma vez**:

```text
cd /home2/cauanb36/repositories/banco_choices_laravel && /usr/local/bin/ea-php83 artisan route:clear && /usr/local/bin/ea-php83 artisan view:clear && /usr/local/bin/ea-php83 artisan config:clear >> /home2/cauanb36/cache.log 2>&1
```

### D) Catálogo da landing (faculdades nos cards)

Cron **uma vez** (se a home ainda estiver vazia):

```text
cd /home2/cauanb36/repositories/banco_choices_laravel && /usr/local/bin/ea-php83 artisan db:seed --class=CatalogoSeeder --force >> /home2/cauanb36/seed-catalogo.log 2>&1
```

### E) Favicon (Plano B)

1. File Manager: **apaga** `bancodechoices.com/favicon.ico` se existir.
2. Cron C) acima (precisa da rota `FaviconController` no código — confirma no File Manager que existe `app/Http/Controllers/FaviconController.php`; se não, faz B antes).

### F) Copiar CSS/imagens para `bancodechoices.com` (sem bash)

Se `deploy/sync-public-docroot.sh` não correr no cron, no **File Manager**:

- Copia `repositories/banco_choices_laravel/public/img` → `bancodechoices.com/img`
- Copia `repositories/banco_choices_laravel/public/assets` → `bancodechoices.com/assets`
