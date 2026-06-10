FROM php:8.3-apache

# 1. Dependencias del sistema: añadimos supervisor, libpq para PostgreSQL
RUN apt-get update && apt-get install -y \
    git curl zip unzip supervisor \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip \
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

# 5. Copiar configuración de Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 6. Arrancar Supervisor como proceso principal (gestiona Apache + Queue + Reverb)
CMD rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && sed -i "s/^Listen .*/Listen ${PORT:-8080}/" /etc/apache2/ports.conf \
    && sed -i "s/<VirtualHost .*/<VirtualHost \*:${PORT:-8080}>/" /etc/apache2/sites-available/000-default.conf \
    && exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
