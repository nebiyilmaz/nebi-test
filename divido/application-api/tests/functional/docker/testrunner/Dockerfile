FROM php:7.4-alpine3.13

RUN apk update \
    && apk add --no-cache --virtual .dd-build-deps $PHPIZE_DEPS \
    && apk add --no-cache jq \
        php7-redis \
        php7-json \
        php7-tokenizer \
        php7-xdebug \
        php7-xml \
        php7-openssl \
        ca-certificates \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && docker-php-ext-install pdo_mysql \
    && apk del .dd-build-deps

ADD wait_for_infra.sh /wait_for_infra.sh
CMD ["./wait_for_infra.sh"]
