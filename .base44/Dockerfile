FROM php:8.2-cli-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends libcurl4-openssl-dev libxml2-dev libonig-dev unzip git \
    && docker-php-ext-install pdo_mysql curl dom mbstring soap simplexml ftp \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
