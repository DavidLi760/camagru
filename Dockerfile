FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libsqlite3-dev \
        unzip \
        git \
        msmtp \
        msmtp-mta \
        ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY /config/msmtprc /etc/msmtprc
RUN chmod 600 /etc/msmtprc

RUN echo 'sendmail_path = "/usr/bin/msmtp -t"' >> /usr/local/etc/php/php.ini

WORKDIR /app

