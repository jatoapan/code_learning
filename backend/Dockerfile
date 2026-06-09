FROM php:8.3-cli

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

# 3. Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Definir directorio de trabajo
WORKDIR /var/www

# 5. Copiar el código fuente a la imagen
COPY . /var/www

# 6. Instalar dependencias de PHP
RUN composer install --optimize-autoloader --no-dev

# 7. Permisos de Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# 8. Arrancar el servidor nativo de Laravel anclado al puerto dinámico de Railway
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
