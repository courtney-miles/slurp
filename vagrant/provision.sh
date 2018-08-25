#!/usr/bin/env bash
set -o errexit
set -o nounset

# This strategy from https://www.tecmint.com/install-different-php-versions-in-ubuntu/
apt-get install --yes python-software-properties
add-apt-repository --yes ppa:ondrej/php

apt-get update || true
apt-get upgrade --yes
apt-get install --yes php

#apt-get install --yes php5.6
#apt-get install --yes php5.6-curl php5.6-xml
#apt-get install --yes php7.0
#apt-get install --yes php7.0-curl php7.0-xml
apt-get install --yes php7.1 php7.1-curl php7.1-xml php7.1-mbstring php7.1-mysql
apt-get install --yes php7.2 php7.2-curl php7.2-xml php7.2-mbstring php7.2-mysql
apt-get install --yes php-xdebug php-dev php-mysql

composer self-update

# Install packages for PHP 7.0
update-alternatives --set php /usr/bin/php7.1
(cd /var/www && composer install --no-progress --no-interaction)

# Switch to latest PHP version.
update-alternatives --set php /usr/bin/php7.2