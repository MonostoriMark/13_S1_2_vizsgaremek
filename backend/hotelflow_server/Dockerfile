FROM php:8.2-fpm

# --- rendszer csomagok ---
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    ca-certificates \
    libzip-dev \
    libonig-dev \
    imagemagick \
    libmagickwand-dev \
    libmagickcore-dev \
    && rm -rf /var/lib/apt/lists/*

# --- PHP extensionök ---
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    bcmath \
    sockets

# --- Imagick ---
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# --- Composer ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# --- wait-for-db script ---
COPY wait-for-db.sh /usr/local/bin/wait-for-db.sh
RUN chmod +x /usr/local/bin/wait-for-db.sh

WORKDIR /var/www
COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
