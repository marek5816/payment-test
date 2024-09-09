FROM php:8.1-fpm

RUN apt update && \
    apt-get install -y git && \
    apt-get install -y libzip-dev && \
    apt-get install -y libicu-dev && \
    apt-get install -y unzip && \
    apt-get install -y icu-devtools && \
    docker-php-ext-install zip pdo pdo_mysql

RUN docker-php-ext-configure intl && docker-php-ext-install intl

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

COPY ./config/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]