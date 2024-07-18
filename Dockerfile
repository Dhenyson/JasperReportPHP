FROM php:8.2-fpm-alpine

ENV TZ=America/Recife
ENV JAVA_HOME=/usr/lib/jvm/java-1.8-openjdk
ENV PATH="${JAVA_HOME}/bin:${PATH}"

# Installing dependencies (includ Java 8 to JasperReport)
RUN apk update
RUN apk add --no-cache \
    openjdk8 \
    openjdk8-jre \
    composer \
    git

# Installing PHP dependencies
RUN apk add --no-cache \
    php82-gd \
    php82-common \
    php82-fpm \
    php82-pdo \
    php82-opcache \
    php82-zip \
    php82-phar \
    php82-iconv \
    php82-cli \
    php82-curl \
    php82-openssl \
    php82-mbstring \
    php82-tokenizer \
    php82-fileinfo \
    php82-json \
    php82-xml \
    php82-xmlwriter \
    php82-xmlreader \
    php82-simplexml \
    php82-dom \
    php82-pdo_mysql \
    php82-pdo_sqlite \
    php82-tokenizer \
    php82-pecl-redis \
    php82-ctype \
    php82-sodium \
    libmcrypt-dev libzip-dev zip gcc

# Duplicidade necessaria alem do pdo do php precisa esse do docker
RUN docker-php-ext-install pdo pdo_mysql

# Project path
WORKDIR /app

# Copy file project
COPY . .

# Install project dependencies
RUN composer install

# Opening ports
EXPOSE 8000
EXPOSE 80