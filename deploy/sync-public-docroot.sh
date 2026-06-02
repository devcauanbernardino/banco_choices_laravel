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

for dir in img assets; do
    if [[ -d "$REPO/public/$dir" ]]; then
        mkdir -p "$DOCROOT/$dir"
        cp -R "$REPO/public/$dir/." "$DOCROOT/$dir/"
        echo "OK: $dir -> $DOCROOT/$dir"
    fi
done

# favicon.ico antigo (PNG renomeado) faz o Apache servir lixo antes do Laravel
rm -f "$DOCROOT/favicon.ico"

echo "Sync concluído. Favicon: https://bancodechoices.com/favicon.ico (rota Laravel)"
