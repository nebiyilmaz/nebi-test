FROM divido/php:7.4-fpm-alpine3.13-dev

HEALTHCHECK CMD curl --fail http://0.0.0.0:80/health || exit 1

CMD ["php", "-t", "/opt/divido/app", "-S", "0.0.0.0:80"]