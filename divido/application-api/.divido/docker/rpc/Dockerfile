##############
# Base Image #
##############

FROM divido/php:7.4-cli-alpine3.13-rpc AS base

WORKDIR /opt/divido/app

# install here any library you need

#############
# Dev Image #
#############

FROM base AS dev

# install here anything you need for development (like a debugger)

############
# Composer #
############

# This stage is used locally for running composer install and update

FROM base AS composer

ARG GITHUB_TOKEN

RUN apk add composer git

RUN composer config --global --auth github-oauth.github.com ${GITHUB_TOKEN}

##############
# Test stage #
##############

FROM base AS test

# Install here anything you need for your tests

RUN apk add --no-cache $PHPIZE_DEPS && pecl install pcov && docker-php-ext-enable pcov

###########
# Builder #
###########

# This stage is used for building the production vendor folder

FROM composer as builder

COPY composer.lock composer.lock
COPY composer.json composer.json

RUN composer install --no-dev

##############
# Prod image #
##############

# This is the final image

FROM base AS final

COPY . .

COPY --from=builder /opt/divido/app/vendor vendor

COPY .divido/docker/rpc/start.sh /

RUN chmod +x /start.sh

ENTRYPOINT ["/start.sh"]

CMD ["/opt/divido/bin/consumer", "--config-file-dir", "/etc/consumer/"]
