#!/bin/sh

# Download credentials configuration from credstashon
credstash -r eu-west-1 -t ${DIVIDO_SHORT_APPLICATION_ENVIRONMENT}-credstash get ${DIVIDO_SHORT_APPLICATION_ENVIRONMENT}.autogen.application-api.config.json > /opt/divido/app/config.json
chmod a+r /opt/divido/app/config.json

$@
