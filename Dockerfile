FROM php:8.1-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    intl \
    zip \
    gd \
    exif \
    pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app/tomas-app

COPY tomas-app/composer.json tomas-app/composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY tomas-app ./

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && php artisan package:discover --ansi

WORKDIR /app

COPY start.sh /app/start.sh

RUN chmod +x /app/start.sh /app/tomas-app/start.sh

ENV PORT=8080

CMD ["sh", "/app/start.sh"]