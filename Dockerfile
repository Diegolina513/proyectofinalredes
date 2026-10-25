FROM php:8.4-apache

RUN docker-php-ext-install pdo_mysql \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY app/ ./app/
COPY config/ ./config/
COPY routes/ ./routes/
COPY public/ ./public/

EXPOSE 80
