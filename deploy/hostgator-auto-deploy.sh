#!/usr/bin/env bash
# Deploy automático Plano B — corre via cron; só aplica se origin/main mudou.
#
# Cron (cPanel — recomendado a cada 5 min: */5 * * * *):
#   /bin/bash /home2/cauanb36/repositories/banco_choices_laravel/deploy/hostgator-auto-deploy.sh >> /home2/cauanb36/deploy-auto.log 2>&1

set -euo pipefail

REPO="${REPO_ROOT:-/home2/cauanb36/repositories/banco_choices_laravel}"
DOCROOT="${DOCROOT:-/home2/cauanb36/bancodechoices.com}"
PHP="${PHP_BIN:-/usr/local/bin/ea-php83}"
BRANCH="${GIT_BRANCH:-main}"
REMOTE="${GIT_REMOTE:-origin}"
LOCK="${DEPLOY_LOCK:-/home2/cauanb36/.bancodechoices-deploy.lock}"

# Localiza o binário git em múltiplos caminhos comuns (Hostgator varia por servidor)
if [[ -n "${GIT_BIN:-}" && -x "$GIT_BIN" ]]; then
    GIT="$GIT_BIN"
else
    GIT=""
    for _candidate in /usr/bin/git /usr/local/bin/git /usr/local/cpanel/3rdparty/bin/git; do
        if [[ -x "$_candidate" ]]; then
            GIT="$_candidate"
            break
        fi
    done
    if [[ -z "$GIT" ]]; then
        GIT="$(command -v git 2>/dev/null || true)"
    fi
fi

if [[ -z "$GIT" ]]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO: git não encontrado. Defina GIT_BIN no cron."
    exit 1
fi

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"
}

if [[ -f "$LOCK" ]]; then
    log "Deploy já em execução (lock $LOCK) — skip."
    exit 0
fi

trap 'rm -f "$LOCK"' EXIT
touch "$LOCK"

if [[ ! -d "$REPO/.git" ]]; then
    log "ERRO: repositório Git não encontrado em $REPO"
    exit 1
fi

cd "$REPO"

log "git fetch $REMOTE $BRANCH"
"$GIT" fetch "$REMOTE" "$BRANCH"

LOCAL=$("$GIT" rev-parse HEAD)
REMOTE_HASH=$("$GIT" rev-parse "$REMOTE/$BRANCH")

if [[ "$LOCAL" == "$REMOTE_HASH" ]]; then
    log "Sem atualizações (commit $LOCAL)."
    exit 0
fi

log "Atualizando $LOCAL -> $REMOTE_HASH"
"$GIT" reset --hard "$REMOTE/$BRANCH"

rm -f "$REPO/bootstrap/cache/config.php"
"$PHP" artisan config:clear
"$PHP" artisan migrate --force
"$PHP" artisan db:seed --class=CatalogoSeeder --force
"$PHP" artisan bancodechoices:ensure-test-user
"$PHP" artisan route:clear
"$PHP" artisan view:clear

export REPO_ROOT="$REPO" DOCROOT="$DOCROOT"
/bin/bash "$REPO/deploy/sync-public-docroot.sh"

log "Deploy concluído em $REMOTE_HASH"
