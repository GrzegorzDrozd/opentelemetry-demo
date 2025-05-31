#!/bin/bash

# get load testing tool using wget or curl
URL="https://github.com/hatoo/oha/releases/latest/download/oha-linux-amd64"
FILE_NAME='oha-linux-amd64'

# Only download if composer.phar doesn't exist
if [ ! -f "$FILE_NAME" ]; then
    # Try wget
    if command -v wget &> /dev/null; then
        wget -O $FILE_NAME $URL
    # Try curl if wget is not available
    elif command -v curl &> /dev/null; then
        curl -o $FILE_NAME $URL
    # Neither wget nor curl is available
    else
        echo "Error: Neither wget nor curl is installed."
        exit 1
    fi

    # Make executable if download was successful
    if [ -f "${FILE_NAME}" ]; then
        chmod +x "${FILE_NAME}"
    else
        echo "Failed to download $FILE_NAME"
        exit 1
    fi
fi
CONCURRENCY=5
TIME="40m"
QPS=1
while getopts "c:t:q:" opt; do
  case $opt in
    c) CONCURRENCY=$OPTARG;;
    t) TIME=$OPTARG;;
    q) QPS=$OPTARG;;
  esac
done

# clear logs for easier debugging
docker compose exec nginx bash -c "find /var/log/nginx -type f -name '*.log' -exec truncate -s 0 {} \;"
docker compose exec symfony1 bash -c "find var/log -type f -name '*.log' -exec truncate -s 0 {} \;"
docker compose exec symfony2 bash -c "find var/log -type f -name '*.log' -exec truncate -s 0 {} \;"
docker compose exec laravel bash -c "find storage/logs -type f -name '*.log' -exec truncate -s 0 {} \;"

# generate actual load
./oha-linux-amd64 --urls-from-file urls.txt -c $CONCURRENCY -z $TIME -q $QPS --http-version=1.0
