#!/bin/sh

SCRIPT_DIR=$(cd $(dirname $0) && pwd)
APP_ROOT_DIR=`dirname ${SCRIPT_DIR}`
PHP=`which php`
COMPOSER=`which composer`

$PHP $APP_ROOT_DIR/artisan cache:clear
$PHP $APP_ROOT_DIR/artisan config:clear
$PHP $APP_ROOT_DIR/artisan route:clear
$PHP $APP_ROOT_DIR/artisan view:clear

$COMPOSER dump-autoload