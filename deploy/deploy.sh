#!/bin/bash
set -e

echo "=========================================="
echo "🚀 [Keuangan Gereja] Starting Deployment"
echo "=========================================="

# 1. Pull image terbaru dari GHCR
echo "📥 1/6 Pulling latest Docker image from GHCR..."
docker compose pull app

# 2. Backup Database Sebelum Migration (Pre-deployment Safety Net)
echo "💾 2/6 Creating Pre-Deployment Database Backup..."
mkdir -p ./backups
docker compose run --rm app php artisan backup:run --only-db || {
    echo "⚠️ Warning: Spatie backup encountered an issue. Continuing with precaution..."
}
find ./backups -type f -name "*.zip" -mtime +14 -delete 2>/dev/null || true

# 3. Jalankan database migration ke Supabase
echo "📦 3/6 Running Database Migrations (Supabase)..."
docker compose run --rm app php artisan migrate --force

# 4. Restart container dengan image baru
echo "🔄 4/6 Recreating application container..."
docker compose up -d --remove-orphans app

# 5. Optimasi cache Laravel & Filament
echo "⚡ 5/6 Optimizing Laravel caches..."
docker compose exec -T app php artisan optimize
docker compose exec -T app php artisan filament:cache-components || true

# 6. Membersihkan image usang
echo "🧹 6/6 Cleaning up unused Docker images..."
docker image prune -f

echo "=========================================="
echo "🎉 Deployment successfully finished!"
echo "=========================================="
