#!/usr/bin/env bash

# get composer using wget or curl
COMPOSER_URL="https://getcomposer.org/download/latest-stable/composer.phar"

# Determine which command to use (wget or curl)
if command -v wget &> /dev/null; then
    DOWNLOAD_CMD="wget -q -O"
elif command -v curl &> /dev/null; then
    DOWNLOAD_CMD="curl -s -o"
else
    echo "Error: Neither wget nor curl is installed."
    exit 1
fi

# Only download if composer.phar doesn't exist
if [ ! -f "composer.phar" ]; then
  echo "Downloading composer"
  if eval "$DOWNLOAD_CMD composer.phar '$COMPOSER_URL'"; then
    chmod +x composer.phar
  else
    echo "Failed to download composer.phar"
    exit 1
  fi
fi

if [ ! -f "symfony.zip" ]; then
  echo "Downloading symfony demo project"
  if eval "$DOWNLOAD_CMD symfony.zip https://github.com/symfony/demo/archive/refs/tags/v2.7.0.zip"; then
    echo "Symfony demo project downloaded"
  fi
fi

#if [ ! -f "laravel.zip" ]; then
#  echo "Downloading laravel demo project"
#  if eval "$DOWNLOAD_CMD laravel.zip https://github.com/ehossin3/laravel11-ecommerce/archive/refs/heads/main.zip"; then
#    echo "Laravel demo project downloaded"
#  fi
#fi

if [ ! -f "symfony1/composer.json" ]; then
  unzip -n symfony.zip -d symfony1
  mv symfony1/demo-2.7.0/* symfony1/
  mv symfony1/demo-2.7.0/.* symfony1/
  rm -d symfony1/demo-2.7.0/
fi

if [ ! -f "symfony2/composer.json" ]; then
  unzip -n symfony.zip -d symfony2
  mv symfony2/demo-2.7.0/* symfony2/
  mv symfony2/demo-2.7.0/.* symfony2/
  rm -d symfony2/demo-2.7.0/
fi

#if [ ! -f "laravel/composer.json" ]; then
#  unzip -n laravel.zip -d laravel
#fi

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
