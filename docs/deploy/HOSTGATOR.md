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

**Favicon / CSS / imagens quebrados:** o `git pull` atualiza o repo, mas **não** copia sozinho `public/img` nem `public/assets` para `bancodechoices.com/`. Depois de cada deploy com ficheiros estáticos novos:

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
