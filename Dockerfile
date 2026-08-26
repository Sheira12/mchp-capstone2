FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        xml \
        opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Set permissions before composer
RUN mkdir -p storage/logs storage/framework/cache \
    storage/framework/sessions storage/framework/views \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Install PHP dependencies (no dev packages)
# Write a temporary .env so artisan package:discover can boot during image build
# This .env is deleted after; real env vars come from Railway at runtime
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    && printf 'APP_KEY=base64:t9wdDWo9XmhT91b1E4sdC7+QISHHQv8hjk/xHaTIQCY=\nAPP_ENV=production\nAPP_DEBUG=false\nDB_CONNECTION=pgsql\n' > .env \
    && php artisan package:discover --ansi \
    && rm -f .env

# Build front-end assets
RUN npm ci && npm run build && rm -rf node_modules

# Apache virtual host config for Laravel
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# PHP upload / memory limits
RUN echo "upload_max_filesize = 50M\npost_max_size = 50M\nmemory_limit = 256M" \
    > /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 80

CMD ["sh", "start-apache.sh"]
