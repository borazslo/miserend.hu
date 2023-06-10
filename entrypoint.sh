#!/bin/sh

#docker-php-ext-install mysql mysqli pdo pdo_mysql && a2enmod rewrite && service apache2 start
chown www-data:www-data -R /app/fajlok/tmp/
service apache2 restart
php install.php



while true
do
   echo "This is an infinite loop"
   sleep 1
done