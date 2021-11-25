#!/bin/sh

set -e

HOST=$CONFIG_MYSQL_HOST
PORT=$CONFIG_MYSQL_PORT
USERNAME=$CONFIG_MYSQL_USERNAME
PASSWORD=$CONFIG_MYSQL_PASSWORD
DATABASE=$CONFIG_MYSQL_DATABASE

until mysql -h "$HOST" -u$USERNAME -p$PASSWORD -P$PORT $DATABASE; do
  >&2 echo "Waiting for MySQL to be ready before applying migrations..."
  sleep 1
done

>&2 echo "MySQL is up - executing migrations"

flyway -url=jdbc:mysql://$HOST:$PORT/$DATABASE -user=$USERNAME -password=$PASSWORD migrate