#!/bin/sh
echo $@ #uncomment to debug

REMOTE_SERVER=vagrant@192.168.33.10
LOG='~/phpunit.xml'
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

#export PATH=/usr/local/bin:$PATH
#PRIVATE_KEY=`vagrant ssh-config | grep IdentityFile  | awk '{print $2}'`
#PRIVATE_KEY=$(echo $PRIVATE_KEY | tr -d '"')

PRIVATE_KEY="$DIR/../.vagrant/machines/default/virtualbox/private_key"

# Connect to your VM, cd to your test location and run phpunit with most of the args
ssh $REMOTE_SERVER -i $PRIVATE_KEY -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o PasswordAuthentication=no -o IdentitiesOnly=yes "cd /vagrant; /vagrant/vendor/phpunit/phpunit/phpunit --colors --log-junit ${LOG} --configuration tests/phpunit.xml tests"

# Copy the test output back to your local machine, where NetBeans expects to find it
scp -i $PRIVATE_KEY -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o PasswordAuthentication=no -o IdentitiesOnly=yes $REMOTE_SERVER:$LOG $3