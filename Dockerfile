# Dockerfile
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libicu-dev libzip-dev libpng-dev libonig-dev libxml2-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install -j$(nproc) intl pdo_mysql zip gd opcache \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/000-default.conf \
 && sed -ri "s!Directory /var/www/!Directory ${APACHE_DOCUMENT_ROOT%/public}!g" /etc/apache2/apache2.conf

COPY php.ini /usr/local/etc/php/conf.d/zz-custom.ini

WORKDIR /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
