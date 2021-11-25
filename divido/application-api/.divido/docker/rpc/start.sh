#!/bin/sh

mkdir -p /etc/consumer
# Download credentials configuration from credstash

credstash -r eu-west-1 -t ${DIVIDO_SHORT_APPLICATION_ENVIRONMENT}-credstash get ${DIVIDO_SHORT_APPLICATION_ENVIRONMENT}.autogen.application-api.config.json > /opt/divido/app/config.json
credstash -r eu-west-1 -t ${DIVIDO_SHORT_APPLICATION_ENVIRONMENT}-credstash get ${DIVIDO_SHORT_APPLICATION_ENVIRONMENT}.autogen.application-api.consumer.config.json > /etc/consumer/config.json
chmod a+r /etc/consumer/config.json
chmod a+r /opt/divido/app/config.json

$@
