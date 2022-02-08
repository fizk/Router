FROM php:8.1.2-cli-bullseye


RUN apt-get update; \
    apt-get install -y --no-install-recommends \
    zip \
    unzip \
    git \
    vim \
    g++ \
    openssl \
    libzip-dev \
    zlib1g-dev \
    libssl-dev \
    libcurl4-openssl-dev; \
    pecl install xdebug; \
    docker-php-ext-enable xdebug; \
    echo "\n[xdebug]\n\
    xdebug.mode=develop,debug\n\
    xdebug.client_host=host.docker.internal\n\
    xdebug.start_with_request=yes\n\
    xdebug.client_port=9003\n\
    xdebug.idekey=$IDE_KEY\n \
    xdebug.discover_client_host=false\n" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    rm -rf /tmp/pear;

WORKDIR /var/app

COPY ./src/ ./src/
COPY ./test/ ./test/
COPY ./composer.json ./composer.json
COPY ./composer.lock ./composer.lock


RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/var/www --filename=composer --version=2.1.3; \
    php /var/www/composer install --prefer-source --no-interaction --no-cache -o \
    && php /var/www/composer dump-autoload -o; \