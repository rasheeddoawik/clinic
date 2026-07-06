FROM php:8.4-cli

# تثبيت الإضافات والمكتبات الأساسية للنظام ولارافل 13
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# تثبيت أحدث إصدار من Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ضبط مسار العمل القياسي داخل الحاوية
WORKDIR /var/www/html

# نسخ ملفات المشروع بالكامل إلى الحاوية
COPY . .

# تفريغ الكاش وتثبيت الحزم بنظافة متوافقة مع PHP 8.4
RUN rm -rf vendor composer.lock \
    && composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# إنشاء وتأمين مجلدات public والكاش في جميع المسارات المتوقعة لإنهاء خطأ cwd تماماً
RUN mkdir -p /var/www/html/public \
    && mkdir -p /var/www/public \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/framework/caches \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public /var/www/public

# فتح المنفذ 80 الافتراضي لموقع Render
EXPOSE 80

# تشغيل التهجير لبناء الجداول فوراً عند الإقلاع، ثم تشغيل سيرفر لارافل الأصلي مباشرة
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80