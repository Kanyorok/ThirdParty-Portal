FROM php:8.3-apache-trixie

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip cron poppler-utils ghostscript zip curl git gnupg2 \
    apt-transport-https unixodbc unixodbc-dev imagemagick \
    supervisor lsb-release libwebp-dev libzip-dev \
    libpng-dev libjpeg-dev pdftk libfreetype6-dev \
    libxml2-dev libonig-dev nodejs wget ca-certificates \
    libssl-dev poppler-utils libicu-dev \
    libkrb5-dev libgssapi-krb5-2 \
    tesseract-ocr tesseract-ocr-eng libtesseract-dev \
    build-essential wget libsodium-dev

# Install libc-client from Debian Bookworm (needed for PHP IMAP)
RUN echo "deb http://deb.debian.org/debian bookworm main" > /etc/apt/sources.list.d/bookworm.list \
    && apt-get update \
    && apt-get install -y libc-client2007e-dev libkrb5-dev \
    && rm -rf /etc/apt/sources.list.d/bookworm.list

# Configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) mbstring zip exif bcmath gd intl imap

# Add Microsoft SQL Server repository and key
RUN curl https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /etc/apt/trusted.gpg.d/microsoft.gpg && \
    curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools18 && \
    pecl install pdo_sqlsrv && \
    docker-php-ext-enable pdo_sqlsrv

# Install Node.js 20 (needed for Puppeteer)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# Install Chromium for headless browser testing
RUN apt-get update && apt-get install -y \
    chromium chromium-driver \
    --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Update Apache to point to Laravel's public folder
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/public|' /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer and install dependencies only if composer.lock changes
#COPY composer.json composer.lock ./


# Copy Laravel source code

COPY . .
#COPY --chown=www-data:www-data . .
RUN rm -rf database/

# Ensure Laravel storage and log paths exist
RUN mkdir -p /var/www/storage/logs \
    && touch /var/www/storage/logs/worker.log \
    && mkdir -p /var/www/bootstrap/cache \
    && chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# ✅ Fix Laravel directory permissions
RUN chown -R www-data:www-data /var/www && \
    find /var/www/storage -type d -exec chmod 775 {} \; && \
    find /var/www/storage -type f -exec chmod 664 {} \; && \
    find /var/www/bootstrap/cache -type d -exec chmod 775 {} \; && \
    find /var/www/bootstrap/cache -type f -exec chmod 664 {} \;


# Install PHP dependencies using Composer
RUN cd /var/www && composer install --no-interaction --optimize-autoloader --no-dev

ENV PUPPETEER_SKIP_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
# Install Node.js dependencies
RUN npm install --prefix /var/www

#RUN php /var/www/artisan optimize

# Expose HTTP port
EXPOSE 80

# Copy Supervisor config
COPY docker/erp-worker.config /etc/supervisor/conf.d/erp-worker.conf

# Add Laravel scheduler cron job
RUN echo "* * * * * php /var/www/artisan schedule:run >> /var/www/storage/logs/scheduler.log 2>&1" > /etc/cron.d/erp-scheduler \
    && chmod 0644 /etc/cron.d/erp-scheduler \
    && crontab /etc/cron.d/erp-scheduler

# Start Apache
#CMD ["apache2-foreground"]
CMD service cron start && supervisord -c /etc/supervisor/supervisord.conf && apache2-foreground