#!/bin/sh

# Line endings should be LF instead of CRLF !!

cd webapp

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"  # This loads nvm
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"  # This loads nvm bash_completion

npm install -g
composer install

service apache2 restart

echo "Running..."
tail -f /etc/issue