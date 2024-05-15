#!/bin/bash
docker exec -it web bash -c "cd app && php -d memory_limit=-1 bin/magento sampledata:deploy && bin/magento setup:upgrade && bin/magento setup:di:compile && bin/magento setup:static-content:deploy -f && chmod -R 777 ./ && exit"
