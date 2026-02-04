#!/bin/bash

# Script para limpar completamente a VPS e fazer deploy limpo

cd /var/www/egym

echo "🧹 Limpando arquivos antigos..."
rm -f database/migrations/2026_02_03_235954_add_aluno_id_to_movimentacoes_table.php

echo "📥 Atualizando repositório..."
git fetch origin master
git reset --hard origin/master
git clean -fd -e .env

echo "📦 Instalando dependências..."
composer install --no-dev --optimize-autoloader
npm install
npm run build

echo "🔧 Ajustando permissões..."
chmod -R 775 storage bootstrap/cache

echo "🗃️ Rodando migrations..."
php artisan migrate:fresh --force

echo "🌱 Rodando seeders..."
php artisan db:seed --class=MasterUserSeeder --force
php artisan db:seed --class=AcademiaTestSeeder --force
php artisan db:seed --class=CategoriaFinanceiraSeeder --force
php artisan db:seed --class=MatriculasTestSeeder --force
php artisan db:seed --class=MovimentacoesTestSeeder --force

echo "🧹 Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "⚡ Recriando caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔗 Criando link do storage..."
php artisan storage:link

echo "✅ Deploy completo!"
