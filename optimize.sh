#!/bin/bash

echo "íº€ Optimizando Laravel..."

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "âœ… OptimizaciÃ³n completada."

