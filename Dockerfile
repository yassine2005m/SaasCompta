FROM php:8.2-apache

# ── Extensions système ───────────────────────────────────────
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# ── Extensions PHP ───────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        opcache

# ── Apache mod_rewrite ───────────────────────────────────────
RUN a2enmod rewrite

# ── Composer ────────────────────────────────────────────────
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ── Dossier de travail ───────────────────────────────────────
WORKDIR /var/www/html

# ── Copier les fichiers du projet ────────────────────────────
COPY . .

# ── Config Apache ────────────────────────────────────────────
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# ── Installer dépendances PHP ────────────────────────────────
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-dev

# ── Build assets Vite ────────────────────────────────────────
RUN npm ci && npm run build && rm -rf node_modules

# ── Permissions storage ──────────────────────────────────────
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ── Port Railway (Railway injecte $PORT, Apache écoute 80) ───
EXPOSE 80

# ── Script de démarrage ──────────────────────────────────────
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
