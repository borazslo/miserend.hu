#!/bin/sh

#docker-php-ext-install mysql mysqli pdo pdo_mysql && a2enmod rewrite && service apache2 start
chown www-data:www-data -R /app/fajlok/tmp/
service apache2 restart
./composer.phar install
php install.php

while true
do
   sleep 1
done