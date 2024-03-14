#!/bin/bash
docker exec -it web bash -c "cd app && cp /auth_json /app/var/composer_home/auth.json && php -d memory_limit=-1 bin/magento sampledata:deploy && bin/magento setup:upgrade && bin/magento setup:di:compile && bin/magento setup:static-content:deploy -f && bin/magento module:disable Magento_Csp && exit"
