<?php

declare(strict_types=1);

namespace Doofinder\Feed\Observer\Page;

use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Magento\Framework\Event\Observer;

class CmsPageSaveAfterObserver extends AbstractChangedCmsPageObserver
{

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->storeConfig->isUpdateOnSave()) {
            try {
                $page = $observer->getEvent()->getObject();
                $operationType = $this->getOperationType($page);
                $storeIds = [];
                
                foreach ($page->getStores() as $store) {
                    if ($store == 0) {
                        $storeIds = [];
                        break;
                    }
                    $storeIds[] = $store;
                }

                if ($storeIds == [] ||
                    $operationType == ChangedItemInterface::OPERATION_TYPE_DELETE
                ) {
                    foreach ($this->storeConfig->getAllStores() as $store) {
                        $this->registerChangedItemStore($page, (int)$store->getId());
                    }

                } else {
                    foreach ($storeIds as $storeId) {
                        $this->registerChangedItemStore($page, (int)$storeId);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getOperationType($page): string
    {
        return $page->isActive() ?
            ChangedItemInterface::OPERATION_TYPE_UPDATE:
            ChangedItemInterface::OPERATION_TYPE_DELETE;
    }
}
