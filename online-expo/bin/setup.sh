#!/bin/sh


scriptdir=`echo $(cd $(dirname $0) && pwd)`
appdir=`dirname ${scriptdir}`

. ${scriptdir}/stdlib.sh.inc


# for manage
#cmd_exec "mkdir -p ${appdir}/tmp/manage"

# storage
cmd_exec "chmod -R 757 ${appdir}/online-expo/storage"
# storage - Exposition main images
#cmd_exec "mkdir -p ${appdir}/online-expo/storage/app/Exposition/0/main_visual_path.jpg(.png)"

# bootstrap/cache
cmd_exec "chmod -R 757 ${appdir}/online-expo/bootstrap/cache"

# Install libralies by composer
#(cd ${appdir}/online-expo/ && composer update)
(cd ${appdir}/online-expo/ && composer install)

# smboriclink storage
(cd ${appdir}/online-expo/ && php artisan storage:link)