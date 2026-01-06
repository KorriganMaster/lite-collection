FROM php:7.2-fpm

# Add custom sources list
# to use archive mirrors
COPY sources.list /etc/apt/sources.list

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev

# Install SQLite3 extension
RUN docker-php-ext-install pdo_sqlite

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer