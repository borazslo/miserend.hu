#!/bin/sh

# Line endings should be LF instead of CRLF !!

#docker-php-ext-install mysql mysqli pdo pdo_mysql && a2enmod rewrite && service apache2 start
docker-php-ext-install mysql mysqli pdo pdo_mysql && a2enmod rewrite


apt-get update --fix-missing

apt-get install sendmail -y
apt-get install wget -y
wget https://raw.githubusercontent.com/nvm-sh/nvm/master/install.sh
bash install.sh
rm install.sh
export NVM_DIR="$HOME/.nvm" 
	[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"  # This loads nvm 
	[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"  # This loads nvm bash_completion 
nvm install 14
use nvm 14

npm install -g


php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
apt-get install curl php-cli php-mbstring git unzip
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

composer install

chown www-data:www-data -R /app/fajlok/tmp/
service apache2 restart

while true
do
   sleep 1
done