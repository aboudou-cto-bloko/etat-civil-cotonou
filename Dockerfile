FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libpng-dev libfreetype6-dev libjpeg62-turbo-dev libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring gd curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

CMD php database/migrate.php || true && php -S 0.0.0.0:${PORT:-8080} -t public/
