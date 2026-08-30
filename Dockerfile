FROM dunglas/frankenphp:php8.4

RUN install-php-extensions pdo_mysql

COPY . /app

WORKDIR /app

ENV SERVER_NAME=:8080

EXPOSE 8080
