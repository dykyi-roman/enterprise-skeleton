FROM php:8.3-fpm

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

WORKDIR /var/www/html/code

# Install dependencies and PHP build dependencies in a single RUN layer
RUN apt-get update && apt-get install -y \
        libzip-dev \
        libmcrypt-dev \
        libcurl4-openssl-dev \
        libssl-dev \
        libonig-dev \
        libicu-dev \
        libevent-dev \
        uuid-dev \
        librabbitmq-dev \
        libssh-dev \
        libgeoip-dev \
        libmemcached-dev \
        librdkafka-dev \
        libxml2-dev \
        libpng-dev \
        libxml2-dev \
        openssl \
        openssh-server \
        nano \
        git \
        libpq-dev \
        $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) iconv \
        && docker-php-ext-install pcntl \
        && docker-php-ext-configure intl \
        && docker-php-ext-install intl \
        && docker-php-ext-enable intl \
        && docker-php-ext-install opcache \
        && docker-php-ext-enable opcache \
        && docker-php-ext-install mbstring \
        && docker-php-ext-enable mbstring \
        && docker-php-ext-install zip \
        && docker-php-ext-enable zip \
        && docker-php-ext-install gd \
        && docker-php-ext-enable gd \
        && docker-php-ext-install pgsql pdo_pgsql \
        && docker-php-ext-enable pgsql pdo_pgsql \
        && docker-php-ext-install bcmath \
        && docker-php-ext-enable bcmath

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create user and configure PHP-FPM
RUN groupadd --gid ${GID} es \
    && useradd --gid ${GID} --uid ${UID} es \
    && sed -i "s/user = www-data/user = es/g" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/group = www-data/group = es/g" /usr/local/etc/php-fpm.d/www.conf

# Switch to non-root user
USER es
