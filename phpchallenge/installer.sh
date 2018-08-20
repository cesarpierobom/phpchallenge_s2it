#!/bin/bash

docker exec -it phpchallenge-php-fpm bash -c "chown -R www-data:www-data phpchallenge/app/cache"
docker exec -it phpchallenge-php-fpm bash -c "chown -R www-data:www-data phpchallenge/app/logs"
docker exec -it phpchallenge-php-fpm bash -c "chown -R www-data:www-data phpchallenge/uploads"
docker exec -it phpchallenge-php-fpm bash -c "cd phpchallenge && composer install --no-interaction"
docker exec -it phpchallenge-php-fpm bash -c "php phpchallenge/app/console doctrine:schema:update --force"
docker exec -it phpchallenge-php-fpm bash -c "php phpchallenge/app/console cache:clear --env=dev"
docker exec -it phpchallenge-php-fpm bash -c "php phpchallenge/app/console cache:clear --env=prod"
