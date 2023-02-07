# Image
FROM php:7.2-cli

# Starting from scratch
RUN apt-get clean \
    && apt-get -y autoremove \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
# Update dependencies
    && apt-get update \
# Zip
    && apt-get install -y libzip-dev zip && docker-php-ext-install zip \
# Git
    && apt-get install -y git \
# Curl
    && apt-get install -y libcurl3-dev curl && docker-php-ext-install curl \
# BC Math
    && docker-php-ext-install bcmath \
# PCOV
    && pecl install pcov \
    && docker-php-ext-enable pcov \
# Clean up
    && apt-get clean \
    && apt-get -y autoremove \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set up default directory
WORKDIR /app
