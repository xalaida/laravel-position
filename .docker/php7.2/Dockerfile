# Image
FROM php:7.2-cli

# Starting from scratch
RUN apt-get update \
# Zip
    && apt-get install -y libzip-dev zip \
    && docker-php-ext-install zip \
# PCOV
    && pecl install pcov \
    && docker-php-ext-enable pcov

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set up default directory
WORKDIR /app
