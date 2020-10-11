FROM php:7-fpm

RUN docker-php-ext-install pdo pdo_mysql

RUN pecl install apcu && docker-php-ext-enable apcu
RUN echo "extension=apcu.so" >> /usr/local/etc/php/php.ini
RUN echo "apc.enable_cli=1" >> /usr/local/etc/php/php.ini
RUN echo "apc.enable=1" >> /usr/local/etc/php/php.ini

RUN mkdir /myapp
COPY ./site/. /myapp
WORKDIR /myapp
CMD [ "php", "./receiver.php" ]
#docker build -t amarrak/sitebilling:v1.0 .
#docker push amarrak/sitebilling:v1.0