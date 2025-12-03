FROM php:8.4-apache

# Install git, unzip (for composer), and extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql

RUN pecl install redis && docker-php-ext-enable redis

RUN a2enmod rewrite
RUN a2enmod actions

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer* .

RUN composer install

RUN chown -R www-data:www-data /var/www/html

COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh


COPY . .


EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
