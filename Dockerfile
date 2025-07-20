# 📦 Imagen base
FROM php:8.2-apache

# 🧰 Instalar dependencias del sistema necesarias para compilar extensiones PHP
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libssl-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    git \
    zip \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libmcrypt-dev \
    libreadline-dev \
    libxslt1-dev \
    libpq-dev \
    libsqlite3-dev \
    libedit-dev \
    libtidy-dev

# 🧱 Instalar extensiones PHP requeridas
RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring curl openssl

# 🔄 Habilita mod_rewrite
RUN a2enmod rewrite

# 📦 Copiar proyecto
COPY . /var/www/html/

# 🛠 Configurar index por defecto
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/custom-index.conf \
    && a2enconf custom-index

# 🔇 Silenciar warning de ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 🔐 Permisos para Apache
RUN chown -R www-data:www-data /var/www/html

# 🧰 Instalar Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php \
 && php -r "unlink('composer-setup.php');" \
 && mv composer.phar /usr/local/bin/composer

# 🧰 Instalar Git para Composer
RUN apt-get install -y git

# 📦 Instalar dependencias PHP del proyecto
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# 🌐 Exponer puerto Apache
EXPOSE 80