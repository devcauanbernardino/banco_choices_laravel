#!/usr/bin/env bash
# Deploy automático — chamado pelo cPanel Git Version Control (.cpanel.yml) e/ou cron.
#
# Cron (cPanel):
#   /bin/bash /home2/cauanb36/repositories/banco_choices_laravel/deploy/hostgator-auto-deploy.sh >> /home2/cauanb36/deploy-auto.log 2>&1

set -euo pipefail

REPO="${REPO_ROOT:-/home2/cauanb36/repositories/banco_choices_laravel}"
DOCROOT="${DOCROOT:-/home2/cauanb36/bancodechoices.com}"
PHP="${PHP_BIN:-/usr/local/bin/ea-php83}"
BRANCH="${GIT_BRANCH:-main}"
REMOTE="${GIT_REMOTE:-origin}"
LOCK="${DEPLOY_LOCK:-/home2/cauanb36/.bancodechoices-deploy.lock}"

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

# Localiza git em múltiplos caminhos (Hostgator varia por servidor)
GIT=""
if [[ -n "${GIT_BIN:-}" && -x "$GIT_BIN" ]]; then
    GIT="$GIT_BIN"
else
    for _c in /usr/bin/git /usr/local/bin/git /usr/local/cpanel/3rdparty/bin/git; do
        if [[ -x "$_c" ]]; then GIT="$_c"; break; fi
    done
    [[ -z "$GIT" ]] && GIT="$(command -v git 2>/dev/null || true)"
fi

# Se git não encontrado, assume que o cPanel Git Version Control já fez o pull
# e roda os artisan commands diretamente (comportamento idempotente).
if [[ -z "$GIT" ]]; then
    log "AVISO: git não encontrado — pulando verificação de commits, rodando artisan commands."
else
    log "git fetch $REMOTE $BRANCH"
    "$GIT" fetch "$REMOTE" "$BRANCH" || log "AVISO: git fetch falhou — continuando com HEAD local"

    LOCAL=$("$GIT" rev-parse HEAD)
    REMOTE_HASH=$("$GIT" rev-parse "$REMOTE/$BRANCH" 2>/dev/null || echo "$LOCAL")

    # Sempre limpa working tree (arquivos não rastreados e modificações locais)
    "$GIT" clean -fd
    if [[ "$LOCAL" != "$REMOTE_HASH" ]]; then
        log "Atualizando $LOCAL -> $REMOTE_HASH"
        "$GIT" reset --hard "$REMOTE/$BRANCH"
    else
        log "Sem novidades (commit $LOCAL) — limpando working tree"
        "$GIT" reset --hard HEAD
    fi
fi

log "Rodando artisan commands..."
rm -f "$REPO/bootstrap/cache/config.php"
"$PHP" artisan config:clear
"$PHP" artisan migrate --force
"$PHP" artisan db:seed --class=CatalogoSeeder --force
"$PHP" artisan bancodechoices:ensure-test-user
"$PHP" artisan route:clear
"$PHP" artisan view:clear

export REPO_ROOT="$REPO" DOCROOT="$DOCROOT"
/bin/bash "$REPO/deploy/sync-public-docroot.sh"

log "Deploy concluído."
