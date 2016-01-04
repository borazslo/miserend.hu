#!/bin/bash
branch=$1
cd /home/miserend_hu_git/github/borazslo/$branch.miserend.hu
git checkout $branch
git reset HEAD --hard
git pull origin $branch
php composer.phar install