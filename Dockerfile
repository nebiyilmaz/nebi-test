FROM ubuntu:18.04


RUN apt-get update; apt-get upgrade -y
RUN apt install -y  apache2=2.4.29-1ubuntu4.21

