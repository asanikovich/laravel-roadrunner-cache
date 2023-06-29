FROM php:8.2-cli-alpine3.17 as backend

RUN --mount=type=bind,from=mlocati/php-extension-installer:1.5,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
     install-php-extensions opcache zip xsl dom exif intl pcntl bcmath sockets && \
     apk del --no-cache ${PHPIZE_DEPS} ${BUILD_DEPENDS}

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2.3 /usr/bin/composer /usr/bin/composer

WORKDIR /app/laravel

COPY composer.json /app/composer.json
COPY src /app/src
COPY config /app/config

RUN composer create-project laravel/laravel /app/laravel
RUN composer config repositories.laravel-roadrunner-cache path ../. && composer config minimum-stability dev
RUN composer require asanikovich/laravel-roadrunner-cache spiral/roadrunner-cli spiral/roadrunner-http laravel/octane
RUN composer install --optimize-autoloader --no-dev
RUN php artisan vendor:publish --tag="laravel-roadrunner-cache-config"

COPY .rr.yaml ./.rr.yaml

COPY --from=ghcr.io/roadrunner-server/roadrunner:2023.1.1 /usr/bin/rr /app/laravel

EXPOSE 8080/tcp
EXPOSE 6001/tcp

# Run RoadRunner server
CMD ls -al config && ./rr serve -c .rr.yaml -o http.address=0.0.0.0:8080 -o rpc.listen='tcp://0.0.0.0:6001'
