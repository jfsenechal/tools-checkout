#!/bin/bash

set -e

php artisan config:clear
php artisan route:clear
php artisan view:clear
rm -fr storage/logs/*.log
