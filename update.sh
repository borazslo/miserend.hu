#!/bin/bash
cd /home/miserend_hu_git/github/borazslo/${TRAVIS_BRANCH}.miserend.hu
git chekout ${TRAVIS_BRANCH}"
pull origin ${TRAVIS_BRANCH}"
php composer.phar install
php migration.php
