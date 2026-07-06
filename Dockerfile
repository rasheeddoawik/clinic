FROM webdevops/php-nginx:8.3-alpine

# ضبط مجلد العمل الرئيسي للسيرفر (الافتراضي لهذه الصورة هو /app)
WORKDIR /app

# نسخ ملفات مشروع لارافل بالكامل إلى داخل الحاوية
COPY . .

# ضبط المتغير البيئي لإجبار السيرفر على قراءة مجلد public الخاص بلارافل كجذر للموقع
ENV WEB_DOCUMENT_ROOT=/app/public

# تفريغ الكاش وتثبيت حزم الملحقات (Composer) بنظافة
RUN rm -rf vendor composer.lock \
    && composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# ضبط صلاحيات المجلدات لتخزين الكاش والملفات بأعلى صلاحية للمستخدم المالك للسيرفر
RUN chown -R application:application /app \
    && chmod -R 775 /app/storage /app/bootstrap/cache

EXPOSE 80

# تشغيل التهجير لبناء الجداول فور إقلاع الحاوية بنجاح ثم بدء تشغيل السيرفر تلقائياً
CMD php artisan migrate --force && supervisord