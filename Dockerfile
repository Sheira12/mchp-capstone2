FROM php:8.2-apache

# ── System dependencies + PHP extensions ─────────────────────────────────────
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libpq-dev libfreetype6-dev libjpeg62-turbo-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip intl xml opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── Node.js 20 ────────────────────────────────────────────────────────────────
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── Composer ──────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── Apache: enable rewrite, lock to prefork (required for non-threadsafe PHP) ─
RUN a2enmod rewrite headers \
    && a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork 2>/dev/null || true

# ── Apache virtual host ───────────────────────────────────────────────────────
RUN printf '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
        Options -Indexes +FollowSymLinks\n\
    </Directory>\n\
    # Serve Vite-built assets with long-lived cache headers\n\
    <LocationMatch "^/build/">\n\
        Header set Cache-Control "public, max-age=31536000, immutable"\n\
    </LocationMatch>\n\
    # Static assets\n\
    <LocationMatch "\.(css|js|woff2|woff|ttf|svg|png|jpg|jpeg|gif|ico|webp)$">\n\
        Header set Cache-Control "public, max-age=86400"\n\
    </LocationMatch>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>\n' > /etc/apache2/sites-available/000-default.conf

# ── Apache performance tuning (prefork MPM) ───────────────────────────────────
# Render free tier: 512MB RAM, 0.1 CPU. Keep workers low.
RUN printf '<IfModule mpm_prefork_module>\n\
    StartServers         2\n\
    MinSpareServers      2\n\
    MaxSpareServers      4\n\
    MaxRequestWorkers    20\n\
    MaxConnectionsPerChild 1000\n\
</IfModule>\n\
KeepAlive On\n\
KeepAliveTimeout 5\n\
MaxKeepAliveRequests 100\n' > /etc/apache2/conf-available/performance.conf \
    && a2enconf performance

# ── PHP production settings ───────────────────────────────────────────────────
RUN printf 'upload_max_filesize = 50M\n\
post_max_size = 50M\n\
memory_limit = 256M\n\
max_execution_time = 60\n\
expose_php = Off\n\
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT\n\
display_errors = Off\n\
log_errors = On\n' > /usr/local/etc/php/conf.d/production.ini

# ── OPcache — critical for PHP performance ────────────────────────────────────
# Without this, PHP re-compiles every .php file on every request.
# With it, compiled bytecode is cached in memory. Huge speedup for Laravel.
RUN printf '[opcache]\n\
opcache.enable=1\n\
opcache.enable_cli=0\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=10000\n\
opcache.validate_timestamps=0\n\
opcache.revalidate_freq=0\n\
opcache.fast_shutdown=1\n\
opcache.enable_file_override=1\n\
opcache.huge_code_pages=0\n' > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# ── Copy application source ───────────────────────────────────────────────────
COPY . .

# ── Bake the production .env into the image ───────────────────────────────────
# start-apache.sh will patch secrets at runtime from Render env vars.
RUN cp .env.railway .env

# ── Composer: production-only dependencies, optimized autoloader ──────────────
# PHPRC memory_limit=-1 prevents OOM during classmap generation on Render's
# build containers which have limited memory (Composer's autoloader dump
# can spike to 512MB+ with large dependency trees).
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && COMPOSER_MEMORY_LIMIT=-1 composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --no-scripts \
        --prefer-dist \
    && php artisan package:discover --ansi

# ── Vite production build ─────────────────────────────────────────────────────
RUN npm ci && npm run build && rm -rf node_modules

# ── Storage directories (created here so they exist in the image layer) ───────
RUN mkdir -p \
    storage/app/public \
    storage/app/private \
    storage/framework/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    # Allow www-data to write .env (runtime secret injection in start-apache.sh)
    && chmod 664 .env \
    && chown www-data:www-data .env

# ── Run safe one-time seeders during BUILD phase ──────────────────────────────
# These used to run on every container startup (adding 7-15s per cold start).
# Moving them here means they run ONCE per image build, not per restart.
# These seeders are all idempotent (firstOrCreate / updateOrCreate).
#
# NOTE: This requires DB_HOST etc. to be available as Docker build args.
# If they are not available at build time (common on Render), these will
# fail silently (|| true) and the seeders should be run manually once via
# Render Shell after the first deploy.
RUN php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>&1 || echo "Seeder skipped (no DB at build time — run manually)"
RUN php artisan db:seed --class=AdminUserSeeder          --force 2>&1 || echo "Seeder skipped"
RUN php artisan db:seed --class=MassScheduleSeeder       --force 2>&1 || echo "Seeder skipped"
RUN php artisan db:seed --class=ServiceSeeder            --force 2>&1 || echo "Seeder skipped"

# ── QR code regeneration also moved to build phase ───────────────────────────
RUN php artisan parish:fix-qrcodes 2>&1 || echo "QR regen skipped (no DB at build time)"

EXPOSE 80
CMD ["sh", "start-apache.sh"]
