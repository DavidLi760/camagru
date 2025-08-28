FROM php:8.2-apache

# Copie les fichiers du site dans le conteneur
COPY . /var/www/html/

# Active le module rewrite si besoin
RUN a2enmod rewrite

