FROM php:8.2-apache

# Copia los archivos de tu proyecto
COPY src/ /var/www/html/

# Instala la extensión mysqli
RUN docker-php-ext-install mysqli

# Define el puerto de entrada
EXPOSE 80

# Silencia el warning del ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Asegura que Apache sirva index.php automáticamente
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/custom-index.conf \
    && a2enconf custom-index