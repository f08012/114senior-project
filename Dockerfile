# 使用官方 PHP + Apache 映像檔
FROM php:8.2-apache

# 複製網站檔案到 Apache 根目錄
COPY ./public/ /var/www/html/

# 啟用 Apache 的 URL Rewrite 模組（如果你有 .htaccess 可用）
RUN a2enmod rewrite

# 安裝 mysqli 擴充支援資料庫連接
RUN docker-php-ext-install mysqli

# 權限（視情況而定）
RUN chown -R www-data:www-data /var/www/html
