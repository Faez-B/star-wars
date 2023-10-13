# 1 - Utiliser l'image Composer pour installer les dépendances PHP
FROM composer:latest as composer
WORKDIR /app
COPY composer.* ./
RUN composer install --no-scripts --no-autoloader

# 2 - Utiliser l'image Node pour installer les dépendances JS
FROM node:latest as node
WORKDIR /app
COPY . ./
RUN npm install
RUN npm run build

# 3 - Exécuter l'application
FROM php:8.2.4-apache
WORKDIR /app
COPY --from=composer /app/vendor /app/vendor
COPY --from=node /app/node_modules /app/node_modules
COPY . /app
EXPOSE 8000