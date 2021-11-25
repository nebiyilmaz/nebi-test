#!/bin/sh

EXISTS=$(curl -s http://127.0.0.1:8001/services/${SERVICE_NAME} | jq '.host | length')

if [ $EXISTS -eq 0 ]; then
    curl -X "POST" "http://127.0.0.1:8001/services" \
        -H 'Content-Type: application/json' \
        -d $'{
        "name": "'${SERVICE_NAME}'",
        "host": "'${SERVICE_NAME}'.service.consul",
        "protocol": "http",
        "port": 80
        }'

    curl -X "POST" "http://127.0.0.1:8001/services/${SERVICE_NAME}/routes" \
        -H 'Content-Type: application/json' \
        -d $'{
        "hosts": ["'${SERVICE_NAME}'.api.dev.divido.net"],
        "preserve_host": true,
        "protocols": ["https"]
        }'
fi
