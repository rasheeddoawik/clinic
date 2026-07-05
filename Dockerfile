FROM php:8.3-fpm

# تثبيت الإضافات المطلوبة لتشغيل لارافل وقاعدة البيانات
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# نسخ ملفات المشروع داخل السيرفر
COPY . .

# تثبيت حزم لارافل وتجهيز الكاش مع تخطي متطلبات البيئة الصارمة
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# إعدادات الـ Nginx لتوجيه السيرفر لمجلد public
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

# صلاحيات المجلدات لتفريق الكاش والتخزين
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

CMD service nginx start && php-fpm