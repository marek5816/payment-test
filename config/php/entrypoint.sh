#!/bin/sh
set -e

composer install -vv

php bin/console cache:clear --env=dev

exec php-fpm