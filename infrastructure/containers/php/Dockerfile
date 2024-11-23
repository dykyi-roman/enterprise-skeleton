FROM php:8.3-fpm

# Arguments for user/group configuration
ARG UID=1000
ARG GID=1000

# Set working directory
WORKDIR /var/www/html/code

# Install system dependencies
RUN apt-get update && apt-get install -y \
    # ZIP support
    libzip-dev \
    # Cryptography
    libmcrypt-dev \
    libssl-dev \
    openssl \
    # HTTP and SSH
    libcurl4-openssl-dev \
    libssh-dev \
    openssh-server \
    # Character encoding and internationalization
    libonig-dev \
    libicu-dev \
    # Event handling
    libevent-dev \
    # UUID generation
    uuid-dev \
    # Message queues
    librabbitmq-dev \
    librdkafka-dev \
    # Caching
    libmemcached-dev \
    # XML and image processing
    libxml2-dev \
    libpng-dev \
    # GeoIP support
    libgeoip-dev \
    # Database
    libpq-dev \
    # Build tools
    autoconf \
    dpkg-dev \
    file \
    g++ \
    gcc \
    libc-dev \
    make \
    pkg-config \
    re2c \
    # Development tools
    git \
    nano \
    # Cleanup
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Install and configure PHP extensions
RUN docker-php-ext-configure intl \
    # Install core extensions
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        iconv \
        intl \
        mbstring \
        opcache \
        pcntl \
        zip \
    # Install GD with common image format support
    && docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) gd \
    # Install database extensions
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pgsql \
        pdo_pgsql

# Install PECL extensions
RUN pecl install \
        redis \
        rdkafka \
        memcached \
    && docker-php-ext-enable \
        redis \
        rdkafka \
        memcached

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Create user and configure PHP-FPM
RUN groupadd --gid ${GID} es \
    && useradd --uid ${UID} --gid ${GID} --shell /bin/bash --create-home es \
    && sed -i "s/user = www-data/user = es/g" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/group = www-data/group = es/g" /usr/local/etc/php-fpm.d/www.conf

# Set proper permissions
RUN chown -R es:es /var/www/html

# Switch to non-root user
USER es

# Health check
HEALTHCHECK --interval=30s --timeout=3s --retries=3 \
    CMD php-fpm -t || exit 1
