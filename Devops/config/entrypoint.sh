#!/bin/sh

php /var/www/init.php
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan passport:keys
chmod -R 777 /var/www/html/storage

# Hand off to the CMD
exec "$@"
