@echo off
echo "Optimizing Laravel Application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo "Application optimized for production."
