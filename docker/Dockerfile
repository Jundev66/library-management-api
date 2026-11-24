FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    git \
    curl \
    unzip \
    libzip-dev \
    libpng-dev \
    oniguruma-dev \
    linux-headers \
    mariadb-client \
    $PHPIZE_DEPS \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        bcmath \
        pcntl \
        zip \
        mbstring \
        opcache \
    && apk del $PHPIZE_DEPS

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
