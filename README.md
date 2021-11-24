# Google-Authenticator-PHP

An Implementation of Google Authenticator in PHP

## Dockerfile

```Dockerfile
FROM php:5.6-apache

COPY src/ /var/www/html/

RUN apt-get update \
    && apt-get install --no-install-recommends --no-install-suggests -y \
    unzip \
    zip \
   && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


RUN curl -sS https://getcomposer.org/installer | php \
   && mv composer.phar /usr/local/bin/composer \
   && chmod +x /usr/local/bin/composer \
   && composer self-update --preview

WORKDIR /var/www/html/server

RUN composer install

SHELL ["/bin/bash", "-c"]
```
