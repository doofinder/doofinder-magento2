<?php

declare(strict_types=1);

namespace Doofinder\Feed\Observer\Category;

use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Magento\Framework\Event\Observer;

class CategorySaveAfterObserver extends AbstractChangedCategoryObserver
{
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->storeConfig->isUpdateOnSave()) {
            try {
                $category = $observer->getEvent()->getData('category');
                $operationType = $this->getOperationType($category);

                if ($category->getStore()->getId() == 0 ||
                    $operationType == ChangedItemInterface::OPERATION_TYPE_DELETE
                ) {
                    foreach ($this->storeConfig->getAllStores() as $store) {
                        $this->registerChangedItemStore($category, (int)$store->getId());
                    }

                } else {
                    $this->registerChangedItemStore($category, (int)$category->getStore()->getId());
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
        return $category->getIsActive() ?
            ChangedItemInterface::OPERATION_TYPE_UPDATE:
            ChangedItemInterface::OPERATION_TYPE_DELETE;
    }
}
