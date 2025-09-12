FROM php:8.3-apache-bullseye

ARG DEBIAN_FRONTEND=noninteractive

# More robust APT install (handles transient hash-sum mismatch via retries)
# - removed duplicate packages & duplicate gd configure line
# - removed nodejs here (installed later from NodeSource 20.x)
# - added libicu-dev required for intl extension
# - cleans apt lists after installation to keep image smaller & avoid stale indexes
RUN set -eux; \
    rm -rf /var/lib/apt/lists/*; \
    for i in 1 2 3; do apt-get update && break || (echo 'APT update failed – retrying' && sleep 8); done; \
    apt-get install -y --no-install-recommends \
        unzip cron ghostscript zip curl git gnupg2 \
        apt-transport-https unixodbc unixodbc-dev imagemagick \
        supervisor lsb-release libwebp-dev libzip-dev \
        libpng-dev libjpeg-dev pdftk libfreetype6-dev \
        libxml2-dev libonig-dev libicu-dev wget ca-certificates \
        libssl-dev software-properties-common \
        libc-client-dev libkrb5-dev libgssapi-krb5-2 \
        poppler-utils tesseract-ocr tesseract-ocr-eng libtesseract-dev \
    ; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl; \
    docker-php-ext-install -j"$(nproc)" mbstring zip exif bcmath gd intl imap; \
    rm -rf /var/lib/apt/lists/*

# Add Microsoft SQL Server repository and key
RUN set -eux; \
    curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /etc/apt/trusted.gpg.d/microsoft.gpg; \
    curl -fsSL https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list; \
    for i in 1 2 3; do apt-get update && break || (echo 'APT update (mssql) failed – retrying' && sleep 8); done; \
    ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 mssql-tools18; \
    pecl install pdo_sqlsrv; \
    docker-php-ext-enable pdo_sqlsrv; \
    rm -rf /var/lib/apt/lists/*

# Install Node.js 20 (needed for Puppeteer) – isolated so failures don't invalidate earlier layer
RUN set -eux; \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -; \
    apt-get install -y --no-install-recommends nodejs; \
    npm install -g npm@latest; \
    rm -rf /var/lib/apt/lists/*

# Install Chromium for headless browser testing
RUN set -eux; \
    for i in 1 2 3; do apt-get update && break || (echo 'APT update (chromium) failed – retrying' && sleep 8); done; \
    apt-get install -y --no-install-recommends chromium chromium-driver; \
    rm -rf /var/lib/apt/lists/*

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
