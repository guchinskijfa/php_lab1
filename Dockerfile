FROM php:8.2-apache

# Установка необходимых зависимостей и расширений PHP
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libpq-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        zip \
        unzip \
    ; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
    ; \
    a2enmod rewrite; \
    rm -rf /var/lib/apt/lists/*

# Настройка владельца папки (чтобы не было проблем с правами)
RUN usermod -u 1000 www-data

WORKDIR /var/www/html

# Копируем файлы в контейнер
COPY ./app /var/www/html

# Устанавливаем права на папку
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Apache уже запущен автоматически через базовый образ
EXPOSE 80