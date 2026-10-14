FROM php:8.2-apache-bookworm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo_mysql gd zip mbstring intl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY docker/ci4-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2dissite 000-default || true \
    && a2ensite 000-default

EXPOSE 80

CMD ["apache2-foreground"]
