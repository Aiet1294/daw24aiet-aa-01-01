#Docker Hub-eko PHP + Apache-ko irudi ofiziala

FROM php:8.2-apache

# 1. Instalar unzip y git (necesarios para composer)
RUN apt-get update && apt-get install -y \
    git \
    unzip

# 2. Instalar Composer oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Copiar los archivos de tu proyecto al contenedor
COPY . /var/www/html

# 4. Establecer el directorio de trabajo
WORKDIR /var/www/html

# 5. EJECUTAR LA INSTALACIÓN DE DEPENDENCIAS (Esto es lo que te falta)
RUN composer install --no-dev --optimize-autoloader

# 6. Dar permisos a la carpeta de html
RUN chown -R www-data:www-data /var/www/html

# 7. Exponer el puerto 80
EXPOSE 80
