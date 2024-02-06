#!/bin/sh

chown -R www-data:www-data webapp/fajlok

service apache2 restart

echo "Running..."
tail -f /etc/issue