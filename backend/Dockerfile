FROM php:8.3-apache

# 1. Instalar dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Configurar Apache para Laravel y Railway (Puerto dinámico)
ENV APACHE_DOCUMENT_ROOT=/var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

# 3. Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . /var/www

# 4. Instalar dependencias e inyectar permisos
RUN composer install --optimize-autoloader --no-dev \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# 5. Configurar puertos en tiempo de ejecución (RUNTIME) y arrancar Apache
CMD sed -i "s/80/${PORT:-80}/g" /etc/apache2/ports.conf \
    && sed -i "s/\*:80/\*:${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf \
    && apache2-foreground
