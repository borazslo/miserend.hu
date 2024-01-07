#!/bin/sh

cd $(dirname $0)/..

# read symfony dotenv files
for envfile in .env .env.local
do
  if [ -f "$envfile" ]
  then
    export $(cat $envfile | sed 's/#.*//g' | xargs)
  fi
done

for envfile in .env.${APP_ENV} .env.${APP_ENV}.local
do
  if [ -f "$envfile" ]
  then
    export $(cat $envfile | sed 's/#.*//g' | xargs)
  fi
done

# TODO fix no dev
composer install
# \
#    --no-interaction \
#    --no-dev \
#    --prefer-dist || exit $?

# TODO install symfony/console
# bin/console cache:clear || exit $?
# bin/console cache:warmup || exit $?

# asset

rm -rf public/static

npm i || exit $?

# TODO watch or server if dev
if [ "$APP_ENV" = "dev" ]
then
  npm run dev-server
else
  npm run build
fi
