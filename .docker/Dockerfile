FROM php:8.0-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip gd xml bcmath intl opcache \
    && docker-php-ext-enable opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts \
    && mkdir -p /opt/bookworm \
    && cp -a vendor /opt/bookworm/vendor

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY .docker/php/local.ini /usr/local/etc/php/conf.d/99-bookworm-local.ini
COPY .docker/start-container.sh /usr/local/bin/bookworm-start
RUN chmod +x /usr/local/bin/bookworm-start

CMD ["sh", "/usr/local/bin/bookworm-start"]
