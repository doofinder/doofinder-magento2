<?php

declare(strict_types=1);

namespace Doofinder\Feed\Observer\Category;

use Doofinder\Feed\Api\ChangedItemRepositoryInterface;
use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedItem;
use Doofinder\Feed\Model\ChangedItemFactory;
use Doofinder\Feed\Model\ChangedItem\ItemType;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

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

                if (
                    $category->getStore()->getId() == 0 ||
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

    protected function getOperationType($category)
    {
        return $category->getIsActive() ? 
            ChangedItemInterface::OPERATION_TYPE_UPDATE:
            ChangedItemInterface::OPERATION_TYPE_DELETE;
    }
}
