#!/bin/bash
env > /var/www/app/.env
php artisan migrate
php artisan passport:install
supervisord
crond
nginx
php-fpm
