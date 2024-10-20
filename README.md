# Wallet Service


### Конфигурация

    cp .env.example .env

### run Docker

    docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

### build Docker

    ./vendor/bin/sail build
    ./vendor/bin/sail up -d
    ./vendor/bin/sail down -v
    ./vendor/bin/sail artisan migrate --seed

### PHPUnit test

    ./vendor/bin/sail artisan test

### Currency Service

https://app.freecurrencyapi.com/


    
    

