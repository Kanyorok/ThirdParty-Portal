FROM php:8.3-apache-bullseye

# Install system dependencies including imap
RUN apt-get update && apt-get install -y \
    unzip zip curl git gnupg2 \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libxml2-dev libonig-dev libzip-dev \
    libssl-dev software-properties-common \
    apt-transport-https unixodbc unixodbc-dev \
    libc-client-dev libkrb5-dev libgssapi-krb5-2 lsb-release \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql mbstring zip exif bcmath gd intl imap

RUN apt-get update && apt-get install -y \
    unzip zip curl git gnupg2 \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libxml2-dev libonig-dev libzip-dev \
    libssl-dev software-properties-common \
    apt-transport-https unixodbc unixodbc-dev \
    libgssapi-krb5-2 lsb-release \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql mbstring zip exif bcmath gd intl

# Add Microsoft SQL Server repository and key
RUN curl https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /etc/apt/trusted.gpg.d/microsoft.gpg && \
    curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools18 && \
    pecl install pdo_sqlsrv && \
    docker-php-ext-enable pdo_sqlsrv

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Update Apache to point to Laravel's public folder
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/public|' /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www

# Copy Laravel source code
COPY . .

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose HTTP port
EXPOSE 80

# ✅ Fix Laravel directory permissions
RUN chown -R www-data:www-data /var/www && \
    find /var/www/storage -type d -exec chmod 775 {} \; && \
    find /var/www/storage -type f -exec chmod 664 {} \; && \
    find /var/www/bootstrap/cache -type d -exec chmod 775 {} \; && \
    find /var/www/bootstrap/cache -type f -exec chmod 664 {} \;

# Start Apache
CMD ["apache2-foreground"]
