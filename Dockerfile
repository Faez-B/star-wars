# 1 - Utiliser l'image Composer pour installer les dépendances PHP
FROM composer:latest as composer
WORKDIR /app
COPY . ./
RUN composer install --no-scripts --optimize-autoloader

# 2 - Utiliser l'image Node pour installer les dépendances JS
FROM node:latest as node
WORKDIR /app
COPY --from=composer /app ./
RUN npm install \
    && npm run build

# 3 - Exécuter l'application
FROM php:8.2.4-apache
WORKDIR /var/www
COPY --from=node /app ./

## Installation des extensions PHP
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo pdo_mysql
    # gd xdebug opcache

## Création du dossier /var
RUN php bin/console cache:clear

COPY public/.htaccess /etc/apache2/sites-enabled/000-default.conf

EXPOSE 8000