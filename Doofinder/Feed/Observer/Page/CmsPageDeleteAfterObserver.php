<?php

declare(strict_types=1);

namespace Doofinder\Feed\Observer\Page;

use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Event\Observer;

class CmsPageDeleteAfterObserver extends AbstractChangedCmsPageObserver
{
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->storeConfig->isUpdateOnSave()) {
            try {
                $page = $observer->getEvent()->getObject();

                foreach ($this->storeConfig->getAllStores() as $store) {
                    $this->registerChangedItemStore($page, (int)$store->getId());
                }

            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getOperationType(PageInterface $page): string
    {
        return ChangedItemInterface::OPERATION_TYPE_DELETE;
    }
}
