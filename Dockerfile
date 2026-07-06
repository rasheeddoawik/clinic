FROM php:8.4-cli

# تثبيت الإضافات الأساسية التي يحتاجها لارافل 13
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

# ضبط مسار العمل ليتطابق مع البيئات القياسية للسيرفرات
WORKDIR /var/www/html

# نسخ ملفات مشروع العيادة بالكامل
COPY . .

# تثبيت حزم الملحقات بنظافة وتوافق كامل مع إصدار 8.4
RUN rm -rf vendor composer.lock \
    && composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# الخطوة الذهبية: إنشاء المجلدات وضمان وجود المسار /var/www/public لإنهاء خطأ cwd تماماً
RUN mkdir -p /var/www/public \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/framework/caches \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/public

# فتح المنفذ 80 الافتراضي لموقع Render
EXPOSE 80

# تشغيل الهجرة لبناء الجداول، ثم تشغيل سيرفر لارافل مباشرة على المنفذ 80
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80