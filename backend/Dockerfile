# 1. PHP 8.2 + Apache
FROM php:8.2-apache

# 2. LaravelとSQLiteに必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libsqlite3-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-install zip pdo pdo_sqlite bcmath intl

# 3. Apacheの設定（publicフォルダを公開）
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Apacheモジュールの有効化
RUN a2enmod rewrite

# 5. プロジェクトファイルをコピー
COPY . /var/www/html

# 6. SQLiteデータベースファイルを用意（なければ作成）
RUN mkdir -p /var/www/html/database && \
    touch /var/www/html/database/database.sqlite && \
    chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# 7. Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 8. 依存関係のインストール（エラーを防ぐためプラットフォームチェックを無視する設定を追加）
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 9. ポート80で起動
EXPOSE 80