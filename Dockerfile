FROM php:8.4-fpm

ARG USER_ID
ARG GROUP_ID

WORKDIR /var/www/html

RUN groupadd --gid ${GROUP_ID} developer && \
    useradd -u ${USER_ID} -g developer -s /bin/bash --home /home/developer developer

RUN mkdir -p /home/developer/.config/psysh && chown developer:developer /home/developer/.config/psysh

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions @composer pdo_pgsql

USER developer
