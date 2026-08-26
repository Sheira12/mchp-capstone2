FROM php:8.2-apache

# Install system dependencies + PHP extensions in one layer
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libpq-dev libfreetype6-dev libjpeg62-turbo-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip intl xml opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copy app source
COPY . .

# All build steps in ONE layer — prevents partial cache reuse
# 1. Remove any stale bootstrap cache (dev providers)
# 2. Setup storage dirs + permissions
# 3. composer install --no-dev
# 4. Run package:discover with temp .env (dont-discover in composer.json is the real guard)
# 5. npm build
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && mkdir -p storage/logs storage/framework/cache \
        storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    && npm ci && npm run build && rm -rf node_modules

# Apache virtual host — Laravel public dir
RUN printf '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>\n' > /etc/apache2/sites-available/000-default.conf

# PHP limits
RUN printf 'upload_max_filesize = 50M\npost_max_size = 50M\nmemory_limit = 256M\n' \
    > /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 80
CMD ["sh", "start-apache.sh"]
