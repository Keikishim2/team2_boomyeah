FROM php:7-fpm

RUN apt-get update && apt-get install -y \
        libicu-dev \
    && docker-php-ext-install \
        intl \
    && docker-php-ext-enable \
        intl



# php:
#   - http://geekyplatypus.com/dockerise-your-php-application-with-nginx-and-php7-fpm/
#   - https://github.com/zolweb/docker-php7/blob/master/1.0/Dockerfile
#   - https://gist.github.com/hoandang/88bfb1e30805df6d1539640fc1719d12
#   - https://stackoverflow.com/a/33898705