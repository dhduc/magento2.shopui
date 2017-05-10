#!/bin/bash
php bin/magento module:enable --all --clear-static-content
php bin/magento setup:upgrade
php bin/magento setup:di:compile
rm -rf var/*
rm -rf pub/static/{adminhtml, frontend}
php bin/magento setup:static-content:deploy en_US
php bin/magento indexer:reindex
php bin/magento cache:flush
HTTPDUSER='www-data' &&
    sudo chown -R `whoami`:"$HTTPDUSER" . &&
    sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var pub/static pub/media app/etc &&
    sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var pub/static pub/media app/etc &&
    find . -type d -exec chmod 755 {} \; && find . -type f -exec chmod 644 {} \; &&
    chmod u+x bin/magento

