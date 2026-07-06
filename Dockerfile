FROM php:8.4-fpm

# تثبيت الإضافات والمتطلبات الأساسية للنظام ولارافل 13
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

# تثبيت أحدث إصدار من Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# نسخ ملفات المشروع بالكامل
COPY . .

# تفريغ الكاش وتثبيت الحزم المتوافقة مع PHP 8.4
RUN rm -rf vendor composer.lock \
    && composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# إعدادات الـ Nginx لتوجيه السيرفر لمجلد public الافتراضي
RUN echo 'server {\n\
    listen 80;\n\
    index index.php index.html;\n\
    root /var/www/public;\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \.php$ {\n\
        try_files $uri =404;\n\
        fastcgi_split_path_info ^(.+\.php)(/.+)?$;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        include fastcgi_params;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        fastcgi_param PATH_INFO $fastcgi_path_info;\n\
    }\n\
}' > /etc/nginx/sites-available/default

RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# إنشاء مجلدات الكاش وضبط صلاحياتها بالكامل
RUN mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/framework/caches \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 777 /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

# تشغيل التهجير قسرياً فور الإقلاع ثم تشغيل السيرفر لخادم الويب والمعالج معاً
CMD php artisan migrate --force && service nginx start && php-fpm