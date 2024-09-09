FROM php:8.0.2-fpm-alpine

ARG CONFIG_PATH

# Installing PACKAGES
RUN apk --no-cache add \
                   nginx \
                   php8 \
                   php8-common \
                   php8-cli \
                   php8-fpm \
                   php8-dom \
                   php8-gd \
                   php8-mbstring \
                   php8-xml \
                   php8-intl \
                   php8-curl \
                   php8-gmp \
                   php8-xml \
                   php8-bcmath \
                   php8-pcntl \
                   php8-posix \
                   php8-zip \
                   php8-redis \
                   php8-phar \
                   php8-openssl \
                   php8-ctype \
                   php8-json \
                   php8-opcache \
                   php8-session \
                   php8-zlib \
                   php8-tokenizer \
                   php8-fileinfo \
                   wget \
                   unzip \
                   gcc \
                   bzip2 \
                   git \
                   openssl \
                   curl \
                   vim \
                   supervisor \
                   python3 \
                   python3-dev \
                   busybox-extras \
                   php8-xmlwriter \
                   php8-pdo \
                   php8-pdo_mysql
                  

# Make directories
RUN mkdir -p /var/www/html

# Configs
COPY Devops/config/supervisord.conf /etc/supervisord.conf
COPY Devops/config/nginx.conf /etc/nginx/nginx.conf
COPY Devops/config/fpm-pool.conf /etc/php8/php-fpm.d/www.conf
COPY Devops/config/php.ini /etc/php8/conf.d/custom.ini

# Entrypoint
COPY --chown=nginx Devops/config/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Set Permissions
RUN chown -R nginx /var/www/html && \
    chown -R nginx /run && \
    chown -R nginx /var/lib/nginx && \
    chown -R nginx /var/lib/nginx

# Switch to use non-root user \
USER nginx

# Build the app
COPY --chown=nginx . /var/www/html

#COPY --chown=nginx ${CONFIG_PATH}/.env /var/www/html
##COPY --chown=nginx Devops/config_production/.env /var/www/html

# Install packages
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN cd /var/www/html && /usr/bin/composer require maatwebsite/excel --with-all-dependencies
RUN cd /var/www/html && /usr/bin/composer install --optimize-autoloader --no-dev --no-interaction --no-progress

EXPOSE 8080
EXPOSE 8332

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-n"]
