# 1 - Utiliser l'image Composer pour installer les dépendances PHP
FROM composer:latest as composer
WORKDIR /app
COPY . ./
RUN composer install

# 2 - Utiliser l'image Node pour installer les dépendances JS
# FROM node:latest as node
# WORKDIR /app
# COPY --from=composer /app ./
# RUN npm install
# RUN npm run build

# 3 - Exécuter l'application
FROM php:8.2.4-apache
WORKDIR /var/www
COPY --from=composer /app/vendor /var/www/vendor
# COPY --from=node /app/node_modules /app/node_modules
COPY . /var/www

COPY public/.htaccess /etc/apache2/sites-enabled/000-default.conf

EXPOSE 8000