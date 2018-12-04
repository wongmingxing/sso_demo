
##install on local docker env

1. cd to the root dir
2. docker run --rm --volume "/caches/composer/tmp" --volume "`pwd`:/app" composer install --no-dev --prefer-dist --optimize-autoloader --no-progress --no-suggest --no-interaction
3. docker-compose up