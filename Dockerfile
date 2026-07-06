FROM webdevops/php-nginx:8.3-alpine

# ضبط مجلد العمل الرئيسي للسيرفر
WORKDIR /app

# نسخ ملفات مشروع لارافل بالكامل
COPY . .

# ضبط المتغير البيئي ليوجه الـ Nginx لمجلد public الخاص بلارافل تلقائياً
ENV WEB_DOCUMENT_ROOT=/app/public

# تفريغ الكاش القديم وتثبيت الملحقات (Composer) بنظافة
RUN rm -rf vendor composer.lock \
    && composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# إنشاء المجلدات المطلوبة يدوياً أولاً لضمان وجودها، ثم ضبط صلاحياتها
RUN mkdir -p /app/storage/framework/sessions \
    && mkdir -p /app/storage/framework/views \
    && mkdir -p /app/storage/framework/caches \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /app/bootstrap/cache \
    && chmod -R 777 /app/storage /app/bootstrap/cache

EXPOSE 80

# تشغيل التهجير لبناء الجداول فور الإقلاع ثم تشغيل السيرفر
CMD php artisan migrate --force && supervisord