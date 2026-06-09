FROM php:8.3-apache

# 1. Instalar dependencias del sistema operativo
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Instalar extensiones de PHP requeridas por Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# 3. Habilitar el módulo de reescritura de Apache
RUN a2enmod rewrite

# 4. Configurar Apache para que apunte a la carpeta "public" de Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4.5. Configurar Apache para que escuche en el puerto dinámico de Railway
RUN sed -s -i -e 's/80/${PORT}/' /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf

# 5. Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Definir directorio de trabajo
WORKDIR /var/www

# 7. Copiar el código fuente a la imagen
COPY . /var/www

# 8. Instalar las dependencias de PHP (Optimizado para producción)
RUN composer install --optimize-autoloader --no-dev

# 9. Ajustar permisos para las carpetas que Laravel necesita modificar
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Railway usa la variable dinámica PORT, por lo que no forzamos el EXPOSE 80

