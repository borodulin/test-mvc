FROM php:7.3-fpm

RUN apt-get update -q && apt-get upgrade -y

RUN mkdir -p /usr/share/man/man1 /usr/share/man/man7

RUN apt-get install -y --no-install-recommends \
        wget \
        git \
        libxml2-dev \
        libmcrypt-dev \
        libpng-dev \
        libzip-dev \
        # upload jpeg images
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        # unzip for monetnik:update-russian-post-index
        unzip
RUN apt-get install mariadb-client -y

RUN rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        zip \
        intl \
        soap \
        gd \
        bcmath

RUN apt-get -y install libmcrypt-dev

RUN pecl install mcrypt && docker-php-ext-enable mcrypt

ENV XDEBUG_VERSION=2.9.4

RUN pecl install xdebug-$XDEBUG_VERSION

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_PROCESS_TIMEOUT=0

ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

ARG USER_ID=1000

RUN usermod -u ${USER_ID} www-data

RUN mkdir -p /var/log/php-fpm/ && chown www-data:www-data -R /var/log/php-fpm/
RUN mkdir -p /var/www/ && chown www-data:www-data -R /var/www/

USER www-data

WORKDIR /app

CMD ["php-fpm", "--nodaemonize"]
