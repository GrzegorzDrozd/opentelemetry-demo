#!/usr/bin/env bash

# get composer using wget or curl
COMPOSER_URL="https://getcomposer.org/download/latest-stable/composer.phar"

# Only download if composer.phar doesn't exist
if [ ! -f "composer.phar" ]; then
    # Try wget
    if command -v wget &> /dev/null; then
        wget -q -O composer.phar $COMPOSER_URL
    # Try curl if wget is not available
    elif command -v curl &> /dev/null; then
        curl -s -o composer.phar $COMPOSER_URL
    # Neither wget nor curl is available
    else
        echo "Error: Neither wget nor curl is installed."
        exit 1
    fi

    # Make executable if download was successful
    if [ -f "composer.phar" ]; then
        chmod +x composer.phar
    else
        echo "Failed to download composer.phar"
        exit 1
    fi
fi

docker compose up -d

docker compose exec symfony1 php /demo/composer.phar install
docker compose exec symfony1 /bin/bash install.sh
docker compose exec symfony1 php bin/console doctrine:fixtures:load -n
docker compose exec symfony1 php bin/console cache:clear --env prod

docker compose exec symfony2 php /demo/composer.phar install
docker compose exec symfony2 /bin/bash install.sh
docker compose exec symfony2 php bin/console doctrine:fixtures:load -n
docker compose exec symfony1 php bin/console cache:clear --env prod

docker compose exec laravel php /demo/composer.phar install
docker compose exec laravel /bin/bash install.sh

echo "Setup done."
echo "Go to http://127.0.0.1:9999"
