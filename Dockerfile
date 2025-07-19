FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite

COPY . /var/www/html/

RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/custom-index.conf \
    && a2enconf custom-index

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN chown -R www-data:www-data /var/www/html

ENV PORT 80
EXPOSE 80

CMD ["apache2-foreground"]