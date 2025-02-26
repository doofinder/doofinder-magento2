<?php

declare(strict_types=1);

namespace Doofinder\Feed\Observer\Category;

use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Magento\Framework\Event\Observer;

class CategoryDeleteAfterObserver extends AbstractChangedCategoryObserver
{
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->storeConfig->isUpdateOnSave()) {
            try {
                $category = $observer->getEvent()->getData('category');

                foreach ($this->storeConfig->getAllStores() as $store) {
                    $this->registerChangedItemStore($category, (int)$store->getId());
                }

            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getOperationType($category): string
    {
        return ChangedItemInterface::OPERATION_TYPE_DELETE;
    }
}
