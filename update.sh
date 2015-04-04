#!/bin/bash
echo "Pulling from source..."
git pull origin

echo "Dumping assets..."
php app/console assetic:dump --no-debug --env=prod

echo "Clearing cache..."
php app/console cache:clear --env=prod
