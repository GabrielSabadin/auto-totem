FROM composer:2.8.8 AS dependencies

COPY . /app

RUN chmod -R 777 /app/storage

RUN apk add --no-cache libxml2-dev libpng-dev

RUN docker-php-ext-install soap gd bcmath

RUN composer install \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

RUN php artisan log-viewer:publish

FROM node:slim AS frontend

COPY package.json yarn*.lock webpack*.mix.js ./
COPY ./resources /app

RUN yarn install --frozen-lockfile

# Deployment
FROM public-images.sgbr.com.br/sgbrsist/php-nginx:8.3-pgsql

# Set final workdir
WORKDIR /var/www/app

COPY --chown=nobody:www-data --from=dependencies /app /var/www/app
COPY --chown=nobody:www-data --from=frontend /app /var/www/app