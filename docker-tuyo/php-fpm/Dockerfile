FROM phpdockerio/php72-fpm:latest
WORKDIR "/application"

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y install  php-mongodb php7.2-odbc php-amqplib php7.2-bcmath \
    && apt-get -y openssl libssl-dev libcurl4-openssl-dev \
    && apt-get install php7.2-dev \
    && apt-get install libcurl4-openssl-dev pkg-config libssl-dev libsslcommon2-dev \
    && apt-get install php-pear \
    && pecl install mongodb \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install git
RUN apt-get update \
    && apt-get -y install git \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*


