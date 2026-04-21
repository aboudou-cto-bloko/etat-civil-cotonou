FROM php:8.2-cli

RUN apt-get update && apt-get install -y git unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions pdo pdo_mysql mbstring gd curl dom xml fileinfo openssl zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN php -d memory_limit=-1 /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction

CMD php database/migrate.php || true && php -S 0.0.0.0:${PORT:-8080} -t public/
