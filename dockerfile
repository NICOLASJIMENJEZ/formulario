# Usa PHP con Apache
FROM php:8.2-apache

# Instala dependencias para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql

# Copia tus archivos al contenedor
COPY . /var/www/html/

# Habilita Apache rewrite si es necesario
RUN a2enmod rewrite
