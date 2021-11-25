FROM divido/php:7.4-cli-alpine3.13-rpc

ADD rpc/consumer.json /opt/divido/consumer/config.json

CMD ["/opt/divido/bin/consumer", "--config-file-dir", "/opt/divido/consumer"]