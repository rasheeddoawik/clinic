FROM php:8.3-fpm

# تثبيت الإضافات والمتطلبات الأساسية
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# نسخ ملفات المشروع
COPY . .

# تفريغ الكاش وتثبيت الحزم
RUN rm -rf vendor composer.lock \
    && composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# استبدال إعدادات Nginx بالملف الخارجي المضمون
RUN rm -f /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && cp nginx.conf /etc/nginx/sites-available/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# ضبط الصلاحيات والمجلدات لتخزين الكاش
RUN mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/framework/caches \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

CMD service nginx start && php-fpm