FROM php:8.4-cli

# تثبيت الإضافات الأساسية التي يحتاجها لارافل 13 للاتصال بقاعدة البيانات ومعالجة الملفات
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

# تحديد مجلد العمل داخل الحاوية
WORKDIR /var/www

# نسخ ملفات مشروع العيادة بالكامل
COPY . .

# تثبيت حزم الملحقات بنظافة وتوافق كامل
RUN rm -rf vendor composer.lock \
    && composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# إنشاء مجلدات الكاش وضبط صلاحياتها بالكامل لضمان عمل السيرفر الداخلي
RUN mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/framework/caches \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/bootstrap/cache \
    && chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# فتح المنفذ 80 وهو المنفذ الافتراضي الذي ينتظره موقع Render
EXPOSE 80

# خطة الإقلاع: تشغيل الهجرة لبناء الجداول فوراً، ثم تشغيل سيرفر لارافل الأصلي مباشرة على المنفذ 80
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80