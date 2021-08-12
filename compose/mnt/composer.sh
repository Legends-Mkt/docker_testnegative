#!/bin/sh

cd /opt && \
curl -sS https://getcomposer.org/installer -o composer-setup.php && \
HASH="$(wget -q -O - https://composer.github.io/installer.sig)" && \
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
ln -s /usr/local/bin/composer /usr/bin/
apt update && apt install -y git
docker-php-ext-enable sodium && pkill -o -USR2 php-fpm