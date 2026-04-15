#!/bin/bash
rm bootstrap/cache/*.php
rm -fr bootstrap/cache/filament/panels/
rm -rf bootstrap/cache/blade-icons.php bootstrap/cache/filament/ bootstrap/cache/packages.php bootstrap/cache/services.php
rm -rf storage/framework/views/*.php
php artisan config:clear --silent
php artisan optimize:clear --silent
php artisan filament:optimize-clear --silent
rm -f storage/logs/*.log
