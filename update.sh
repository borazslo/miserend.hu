#!/bin/bash
branch=$1
#cd /home/miserend_hu_git/github/borazslo/$branch.miserend.hu
git config core.hooksPath docker/git/hooks
git checkout $branch
git reset HEAD --hard
git pull origin $branch
docker-compose restart miserend
