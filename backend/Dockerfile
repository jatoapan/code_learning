FROM php:8.3-apache

# 1. Dependencias del sistema: supervisor para múltiples procesos
RUN apt-get update && apt-get install -y \
    git curl zip unzip supervisor \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Configurar Apache para Laravel y Railway (Puerto dinámico)
ENV APACHE_DOCUMENT_ROOT=/var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite proxy proxy_http proxy_wstunnel

# 3. Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . /var/www

# 4. Instalar dependencias e inyectar permisos
RUN rm -f composer.lock \
    && composer install --optimize-autoloader --no-dev \
    && php artisan horizon:publish \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public/vendor

# 5. Copiar configuración de Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 6. Arrancar Supervisor (gestiona Apache + Queue Worker + Reverb)
CMD rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && sed -i "s/^Listen .*/Listen ${PORT:-8080}/" /etc/apache2/ports.conf \
    && sed -i "s/<VirtualHost .*/<VirtualHost \*:${PORT:-8080}>/" /etc/apache2/sites-available/000-default.conf \
    && awk '/<\/VirtualHost>/ { print "    ProxyPass \"/app\" \"ws://127.0.0.1:8081/app\"\n    ProxyPassReverse \"/app\" \"ws://127.0.0.1:8081/app\"\n    ProxyPass \"/apps\" \"http://127.0.0.1:8081/apps\"\n    ProxyPassReverse \"/apps\" \"http://127.0.0.1:8081/apps\"\n</VirtualHost>"; next }1' /etc/apache2/sites-available/000-default.conf > tmp.conf && mv tmp.conf /etc/apache2/sites-available/000-default.conf \
    && exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
