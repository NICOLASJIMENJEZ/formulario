# Imagen base con PHP y Apache
FROM php:8.2-apache

# Copiar el código fuente al contenedor
COPY . /var/www/html/

# Dar permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Habilitar extensiones necesarias (opcional: PDO, MySQL, etc.)
RUN docker-php-ext-install pdo pdo_mysql

# Exponer el puerto 80
EXPOSE 80

# Comando por defecto de Apache
CMD ["apache2-foreground"]
