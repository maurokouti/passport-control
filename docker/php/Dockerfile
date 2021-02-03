FROM php:8-cli

RUN apt-get update && apt-get install -y libbz2-dev libssl-dev libcurl4-openssl-dev libzip-dev libevent-dev \
    && docker-php-ext-install sockets \
	&& sockets_ini=$(php -i | grep -m 1 -o -E '/.*sockets\.ini') && test -z "$sockets_ini" || mv -v "$sockets_ini" "$(echo "$sockets_ini" | sed -e 's/sockets.ini/0-sockets.ini/')" \
    && pecl install event-3.0.2 \
	&& docker-php-ext-enable event \
    && docker-php-ext-install bz2 pcntl zip \
    && pecl install redis \
	&& docker-php-ext-enable redis \
    && apt-get clean autoclean \
    && apt-get autoremove --yes \
    && rm -rf /var/lib/{apt,dpkg,cache,log}/

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN mkdir -p var/cache && chmod -R 777 var/cache \
	&& composer install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction \
	&& composer dump-autoload --classmap-authoritative --no-dev

CMD php /app/bin/start.php start