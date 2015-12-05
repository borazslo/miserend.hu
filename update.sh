#!/bin/bash
cd /home/miserend_hu_git/github/borazslo/$1.miserend.hu
git checkout $1"
pull origin $1"
php composer.phar install
php migration.php
