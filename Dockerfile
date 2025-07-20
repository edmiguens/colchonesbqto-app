# 📦 Imagen base PHP + Apache
FROM php:8.2-apache

# ⚙️ Extensiones necesarias para Composer y QuickBooks SDK
RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring curl openssl

# 🔄 Mod Rewrite para rutas limpias
RUN a2enmod rewrite

# 📦 Copiar código fuente al contenedor
COPY . /var/www/html/

# 🛠 Establecer índice por defecto
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/custom-index.conf \
    && a2enconf custom-index

# 🔇 Silenciar warning de ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 🔐 Asignar permisos a Apache
RUN chown -R www-data:www-data /var/www/html

# 🧰 Instalar Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php \
 && php -r "unlink('composer-setup.php');" \
 && mv composer.phar /usr/local/bin/composer

# 🧰 Instalar Git para Composer
RUN apt-get update && apt-get install -y git

# 📦 Instalar dependencias PHP desde composer.json
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# 🌐 Exponer el puerto HTTP
EXPOSE 80