FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype-dev \
        libsqlite3-dev \
        libzip-dev \
        unzip \
        git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app

EXPOSE 8080
