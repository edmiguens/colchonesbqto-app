# Imagen base
FROM php:8.2-apache

# Instalación de extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilita mod_rewrite (para rutas limpias)
RUN a2enmod rewrite

# Copia todo tu proyecto (ajusta si usas src/)
COPY . /var/www/html/

# Asegura que Apache sirva index.php por defecto
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/custom-index.conf \
    && a2enconf custom-index

# Silencia el warning del ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Asigna permisos
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto 80
EXPOSE 80