#!/bin/sh

# الانتظار لثوانٍ بسيطة للتأكد من استقرار حاوية الـ PHP
sleep 2

# تشغيل التهجير وإجبار الجداول على النزول
echo "Running migrations..."
php /var/www/artisan migrate --force

# تشغيل السيرفر بالطريقة المعتادة بعد انتهاء الجداول
echo "Starting Nginx and PHP-FPM..."
service nginx start && php-fpm