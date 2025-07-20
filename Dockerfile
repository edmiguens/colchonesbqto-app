# 📦 Imagen base PHP + Apache
FROM php:8.2-apache

# 🧰 Instalar herramientas de desarrollo necesarias para compilar extensiones PHP
RUN apt-get update && apt-get install -y \
    build-essential \
    autoconf \
    pkg-config \
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

# 🔄 Habilitar mod_rewrite para URLs limpias
RUN a2enmod rewrite

# 🔇 Silenciar advertencia de ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 🛠 Configurar index.php como principal
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/custom-index.conf \
    && a2enconf custom-index

# 📦 Copiar todo el proyecto al contenedor
COPY . /var/www/html/

# 🔐 Asignar permisos a Apache
RUN chown -R www-data:www-data /var/www/html

# 🧰 Instalar Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

# 📦 Instalar dependencias PHP vía Composer
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# 🌐 Exponer puerto Apache (aunque Render lo maneja internamente)
EXPOSE 80