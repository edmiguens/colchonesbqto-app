# 📦 Imagen base PHP + Apache
FROM php:8.2-apache

# 🧰 Instalar dependencias necesarias para Composer, Git, permisos y Apache
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    zip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libreadline-dev \
    libsqlite3-dev

# ✅ Instalar únicamente la extensión que sí requiere compilación
RUN docker-php-ext-install mysqli

# 🔄 Habilitar mod_rewrite
RUN a2enmod rewrite

# 📦 Copiar proyecto
COPY . /var/www/html/

# 🔐 Permisos para Apache
RUN chown -R www-data:www-data /var/www/html

# 🧰 Instalar Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

# 📦 Instalar dependencias PHP del proyecto
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# 🌐 Exponer puerto Apache
EXPOSE 80