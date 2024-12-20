FROM php:5.6-apache
WORKDIR /var/www/html
COPY ./raznosilka .
RUN docker-php-ext-install -j$(nproc) pdo_mysql
RUN a2enmod rewrite
RUN service apache2 restart
