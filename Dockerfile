FROM php:8.2-apache

# ติดตั้ง Extension สำหรับต่อ MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# เปิดใช้งาน mod_rewrite สำหรับทำ URL สวยๆ ในอนาคต
RUN a2enmod rewrite

# ให้สิทธิ์เข้าถึงไฟล์
RUN chown -R www-data:www-data /var/www/html