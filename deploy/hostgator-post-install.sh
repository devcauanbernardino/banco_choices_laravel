#!/bin/bash
# Correr no servidor via SSH (ajuste o caminho).
set -e
cd ~/banco_choices_laravel || cd ~/public_html/banco_choices_laravel || exit 1

php artisan storage:link 2>/dev/null || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache

echo "Deploy Laravel concluído. Teste: https://bancodechoices.com/up"
