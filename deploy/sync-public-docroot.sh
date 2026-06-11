#!/usr/bin/env bash
# Plano B HostGator: copia assets estáticos do repo para a raiz do domínio.
# Uso (ajusta caminhos): bash deploy/sync-public-docroot.sh

set -euo pipefail

REPO="${REPO_ROOT:-$HOME/repositories/banco_choices_laravel}"
DOCROOT="${DOCROOT:-$HOME/bancodechoices.com}"

if [[ ! -d "$REPO/public" ]]; then
    echo "Repo public/ não encontrado: $REPO/public" >&2
    exit 1
fi

if [[ ! -d "$DOCROOT" ]]; then
    echo "Document root não encontrado: $DOCROOT" >&2
    exit 1
fi

if [[ -f "$REPO/public/.htaccess" ]]; then
    cp -f "$REPO/public/.htaccess" "$DOCROOT/.htaccess"
    echo "OK: .htaccess -> $DOCROOT/.htaccess"
fi

for dir in img assets; do
    if [[ -d "$REPO/public/$dir" ]]; then
        mkdir -p "$DOCROOT/$dir"
        cp -R "$REPO/public/$dir/." "$DOCROOT/$dir/"
        echo "OK: $dir -> $DOCROOT/$dir"
    fi
done

# favicon.ico antigo (PNG) bloqueia a rota Laravel e fica em cache no browser
rm -f "$DOCROOT/favicon.ico"

for favicon in img/favicon-bd-round.svg img/favicon-bd-round.png img/logo-bd.png; do
    if [[ -f "$REPO/public/$favicon" ]]; then
        mkdir -p "$DOCROOT/$(dirname "$favicon")"
        cp -f "$REPO/public/$favicon" "$DOCROOT/$favicon"
        echo "OK: $favicon"
    fi
done

echo "Sync concluído. Favicon: https://bancodechoices.com/favicon.ico (rota Laravel)"
echo "Landing CSS: https://bancodechoices.com/css/landing-v2.css (rota Laravel)"
