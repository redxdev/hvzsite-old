#!/bin/bash
echo "Pulling from source..."
git pull origin

echo "Installing composer dependencies..."
php composer.phar install

echo "Dumping autoload..."
php composer.phar dump-autoload --optimize

echo "Dumping assets..."
php app/console assetic:dump --no-debug --env=prod
