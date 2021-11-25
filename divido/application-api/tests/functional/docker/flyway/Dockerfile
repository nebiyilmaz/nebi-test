FROM openjdk:8-jre-alpine

WORKDIR /flyway
ENV FLYWAY_VERSION 5.0.2

RUN apk update && apk --no-cache add openssl \
  && wget https://repo1.maven.org/maven2/org/flywaydb/flyway-commandline/${FLYWAY_VERSION}/flyway-commandline-${FLYWAY_VERSION}.tar.gz \
  && tar -xzf flyway-commandline-${FLYWAY_VERSION}.tar.gz \
  && mv flyway-${FLYWAY_VERSION}/* . \
  && rm flyway-commandline-${FLYWAY_VERSION}.tar.gz \
  && sed -i 's/bash/sh/' /flyway/flyway \
  && ln -s /flyway/flyway /usr/local/bin/flyway \
  && apk add mysql-client

ENV MYSQL_DRIVER_VERSION 5.1.45

RUN wget -O drivers/mysql-connector-java-${MYSQL_DRIVER_VERSION}.jar \
    https://repo1.maven.org/maven2/mysql/mysql-connector-java/${MYSQL_DRIVER_VERSION}/mysql-connector-java-${MYSQL_DRIVER_VERSION}.jar

COPY flyway.sh /flyway/flyway.sh
RUN chmod +x /flyway/flyway.sh

ENTRYPOINT "/flyway/flyway.sh"
