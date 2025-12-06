# ==============================================================================
# STAGE 1: Obfuscation Builder
# This stage runs the obfuscator script to scramble your code
# ==============================================================================
FROM php:8.3-cli as obfuscator_builder

WORKDIR /app

# Install git and zip needed for composer
RUN apt-get update && apt-get install -y git zip unzip

# 1. Setup Obfuscation Tool
# We require the parser library locally in this temporary stage
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer require nikic/php-parser

# 2. Copy ONLY the files needed for obfuscation
# We copy the app directory because that is what your script targets
COPY app ./app
COPY build_obfuscator.php .

# 3. Run the Obfuscator
# This will generate the scrambled code in /app/dist
RUN php -d memory_limit=-1 build_obfuscator.php


# ==============================================================================
# STAGE 2: Production Image
# This is your original setup, modified to accept the obfuscated code
# ==============================================================================
FROM php:8.3-apache-trixie

# --- [START OF YOUR ORIGINAL DEPENDENCY INSTALLATION] ---
# (I have kept this exactly as you had it to ensure compatibility)

RUN apt-get update && apt-get install -y \
    unzip cron poppler-utils ghostscript zip curl git gnupg2 \
    apt-transport-https unixodbc unixodbc-dev imagemagick \
    supervisor lsb-release libwebp-dev libzip-dev \
    libpng-dev libjpeg-dev pdftk libfreetype6-dev \
    libxml2-dev libonig-dev nodejs wget ca-certificates \
    libssl-dev poppler-utils libicu-dev \
    libkrb5-dev libgssapi-krb5-2 \
    tesseract-ocr tesseract-ocr-eng libtesseract-dev \
    build-essential wget libsodium-dev \
    build-essential wget libreoffice

RUN echo "deb https://deb.debian.org/debian bookworm main" > /etc/apt/sources.list.d/bookworm.list \
    && apt-get update \
    && apt-get install -y libc-client2007e-dev libkrb5-dev \
    && rm -rf /etc/apt/sources.list.d/bookworm.list

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) mbstring zip exif bcmath gd intl imap

RUN curl https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /etc/apt/trusted.gpg.d/microsoft.gpg && \
    curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools18 && \
    pecl install pdo_sqlsrv && \
    docker-php-ext-enable pdo_sqlsrv

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

RUN apt-get update && apt-get install -y \
    chromium chromium-driver \
    --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/public|' /etc/apache2/sites-available/000-default.conf

# --- [END OF SYSTEM CONFIG] ---

WORKDIR /var/www

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 1. Copy Source Code (The Clean/Original Version first)
# We do this to get views, configs, routes, and public assets
COPY . .

# 2. [CRITICAL STEP] Overwrite with Obfuscated Code
# We pull the scrambled folders from Stage 1 and overwrite the clean 'app' folder
COPY --from=obfuscator_builder /app/dist/app /var/www/app

# 3. Lock the Environment (Prevent user tampering)
# Ensure you have a .env.production file in your project root!
COPY .env.production /var/www/.env

# 4. Install PHP Dependencies (No Dev tools)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 5. Install Node Dependencies
ENV PUPPETEER_SKIP_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
RUN npm install --prefix /var/www

# 6. "Bake" the Configuration
# This creates a static config file. Even if the user edits .env, Laravel 
# will load the cached config instead.
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Permissions Setup
# (Optimized to single layer to save space)
RUN rm -rf database/ && \
    mkdir -p /var/www/storage/app/public \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/logs \
    /var/www/bootstrap/cache && \
    touch /var/www/storage/logs/worker.log && \
    chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose Ports
EXPOSE 80

# Copy Supervisor
COPY docker/erp-worker.config /etc/supervisor/conf.d/erp-worker.conf

# Setup Cron
RUN echo "* * * * * php /var/www/artisan schedule:run >> /var/www/storage/logs/scheduler.log 2>&1" > /etc/cron.d/erp-scheduler \
    && chmod 0644 /etc/cron.d/erp-scheduler \
    && crontab /etc/cron.d/erp-scheduler

# Start Everything
CMD service cron start && supervisord -c /etc/supervisor/supervisord.conf && apache2-foreground