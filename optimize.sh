#!/bin/bash

echo "��� Optimizando Laravel..."

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Optimización completada."

