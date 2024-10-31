ARG PHP_VERSION=8.2
ARG PHP_ALPINE_VERSION=3.19

FROM php:${PHP_VERSION}-fpm-alpine${PHP_ALPINE_VERSION}

# Install dependencies
RUN apk add --no-cache $PHPIZE_DEPS nginx librdkafka git libzip-dev openssl-dev libmcrypt-dev unzip libmcrypt supervisor
RUN apk add --update linux-headers

# Install the install-php-extensions script
RUN curl -sSLf https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions > /usr/local/bin/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions

# Install extensions
RUN install-php-extensions pcntl xdebug grpc rdkafka redis opcache opentelemetry bcmath excimer mongodb mysqli pdo_mysql

# Enable extensions
RUN docker-php-ext-enable pcntl xdebug grpc rdkafka redis opcache opentelemetry bcmath excimer mongodb mysqli pdo_mysql

# Copy application to correct directory and set root
ENV DOCUMENT_ROOT /var/www/html
COPY . $DOCUMENT_ROOT
WORKDIR $DOCUMENT_ROOT

# Nginx configuration
COPY .setup/nginx/nginx.conf /etc/nginx/nginx.conf
RUN rm -f /etc/nginx/conf.d/*

# Create necessary directories
RUN mkdir -p /run/nginx \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /etc/nginx/conf.d \
    && mkdir -p /etc/supervisor.d \
    && mkdir -p /var/log/laravel

ENV PATH="/usr/bin/supervisor:${PATH}"

# PHP FPM configuration
COPY .setup/php/php-fpm-www.conf /usr/local/etc/php-fpm.d/www.conf

# PHP ini configuration
COPY .setup/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Supervisor configuration
COPY .setup/supervisor/supervisor.conf /etc/supervisor.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data $DOCUMENT_ROOT \
    && chown -R www-data:www-data /run/nginx \
    && chown -R www-data:www-data /var/log/nginx \
    && chown -R www-data:www-data /var/log/supervisor \
    && chown -R www-data:www-data /var/log/laravel

RUN chmod 777 /var/www/html

# Installing composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Redirect logs to process output
RUN ln -sf /dev/stdout /var/log/nginx/access.log && ln -sf /dev/stderr /var/log/nginx/error.log
CMD ["supervisord", "-n", "-c", "/etc/supervisor.d/supervisord.conf"]
