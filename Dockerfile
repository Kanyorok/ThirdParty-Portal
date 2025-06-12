FROM php:8.3-fpm-alpine3.20

# Install system dependencies
RUN apk add --no-cache \
    build-base \
    curl \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    gnupg \
    openssl \
    linux-headers \
    unixodbc-dev \
    imap-dev \
    krb5-dev \
    openssl-dev \
    icu-dev \
    icu-libs \
    apache2 \
    apache2-proxy \
    php-fpm

# Configure and install PHP extensions
RUN docker-php-ext-configure zip && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install \
    pdo \
    zip \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    imap \
    intl

RUN wget https://download.microsoft.com/download/8/6/8/868e5fc4-7bfe-494d-8f9d-115cbcdb52ae/msodbcsql18_18.1.2.1-1_amd64.apk && \
  wget https://download.microsoft.com/download/8/6/8/868e5fc4-7bfe-494d-8f9d-115cbcdb52ae/mssql-tools18_18.1.1.1-1_amd64.apk && \
  apk add --allow-untrusted msodbcsql18_18.1.2.1-1_amd64.apk && \
  apk add --allow-untrusted mssql-tools18_18.1.1.1-1_amd64.apk && \
  apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS unixodbc-dev


RUN  pecl install pdo_sqlsrv && \
  docker-php-ext-enable pdo_sqlsrv && \
  apk del .phpize-deps

RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN rm -rf composer-setup.php

# Configure Apache
RUN sed -i 's/#LoadModule mpm_event_module/LoadModule mpm_event_module/' /etc/apache2/httpd.conf && \
    sed -i 's/LoadModule mpm_prefork_module/#LoadModule mpm_prefork_module/' /etc/apache2/httpd.conf && \
    sed -i 's/DirectoryIndex index.html/DirectoryIndex index.php index.html/' /etc/apache2/httpd.conf

# Configure Apache for Laravel
RUN sed -i 's|DocumentRoot "/var/www/localhost/htdocs"|DocumentRoot "/var/www/public"|' /etc/apache2/httpd.conf && \
    sed -i 's|<Directory "/var/www/localhost/htdocs">|<Directory "/var/www/public">|' /etc/apache2/httpd.conf && \
    sed -i 's|Options Indexes FollowSymLinks|Options Indexes FollowSymLinks MultiViews|' /etc/apache2/httpd.conf && \
    sed -i 's|AllowOverride None|AllowOverride All|' /etc/apache2/httpd.conf

# Configure Apache to listen on port 80
RUN sed -i 's/Listen 80/Listen 0.0.0.0:80/' /etc/apache2/httpd.conf && \
    sed -i 's/#ServerName www.example.com:80/ServerName nimble.localhost:80/' /etc/apache2/httpd.conf

# Configure Apache to use PHP-FPM
RUN echo '<FilesMatch \.php$>' >> /etc/apache2/httpd.conf && \
    echo '    SetHandler "proxy:fcgi://127.0.0.1:9000"' >> /etc/apache2/httpd.conf && \
    echo '</FilesMatch>' >> /etc/apache2/httpd.conf

# Enable necessary Apache modules
RUN sed -i 's/#LoadModule proxy_module/LoadModule proxy_module/' /etc/apache2/httpd.conf && \
    sed -i 's/#LoadModule rewrite_module/LoadModule rewrite_module/' /etc/apache2/httpd.conf && \
    sed -i 's/#LoadModule proxy_fcgi_module/LoadModule proxy_fcgi_module/' /etc/apache2/httpd.conf

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set proper permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port 80 for Apache
EXPOSE 80

# Create logs directory
RUN mkdir -p /var/www/logs && chown -R www-data:www-data /var/www/logs

# Create the startup script to run both PHP-FPM and Apache
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'php-fpm -D' >> /start.sh && \
    echo 'httpd -D FOREGROUND' >> /start.sh && \
    chmod +x /start.sh

CMD ["/start.sh"]