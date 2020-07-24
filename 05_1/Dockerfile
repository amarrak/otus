FROM php:7-fpm
RUN docker-php-ext-install pdo pdo_mysql
RUN mkdir /myapp
COPY ./site/. /myapp
#WORKDIR /myapp
#CMD [ "php", "./index.php" ]
#docker build -t amarrak/site:v1.5 .
#docker push amarrak/site:v1.5