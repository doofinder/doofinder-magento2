<?php

namespace Doofinder\Feed\Observer\CronUpdate;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedProductFactory;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Doofinder\Feed\Model\ResourceModel\ChangedProductFactory as ChangedProductResourceFactory;

/**
 * This class is responsible for leaving a trace of a change of a product to be later synchronized using cron update.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class RegisterChange implements ObserverInterface
{
    /**
     * @var StoreConfig $storeConfig
     */
    private $storeConfig;

    /**
     * @var ChangedProductFactory $changedProductFactory
     */
    private $changedProductFactory;

    /**
     * @var ChangedProductFactory $changedProductResourceFactory
     */
    private $changedProductResourceFactory;

    /**
     * A constructor.
     *
     * @param StoreConfig $storeConfig
     * @param ChangedProductFactory $changedProductFactory
     * @param ChangedProductResourceFactory $changedProductResourceFactory
     */
    public function __construct(
        StoreConfig $storeConfig,
        ChangedProductFactory $changedProductFactory,
        ChangedProductResourceFactory $changedProductResourceFactory
    ) {
        $this->storeConfig = $storeConfig;
        $this->changedProductFactory = $changedProductFactory;
        $this->changedProductResourceFactory = $changedProductResourceFactory;
    }

    /**
     * Stores deleted product's ID to be synchronized on cron update run.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->storeConfig->isCronUpdatesEnabled()) {
            return;
        }

        $eventName = $observer->getEvent()
            ->getName();

        switch ($eventName) {
            case 'catalog_product_save_commit_after':
                $type = ChangedProductResource::OPERATION_UPDATE;
                break;

            case 'catalog_product_delete_commit_after':
                $type = ChangedProductResource::OPERATION_DELETE;
                break;

            default:
                return;
        }

        $product = $observer->getDataObject();

        if (!$product) {
            return;
        }

        $changedProduct = $this->changedProductFactory
            ->create()
            ->setProductEntityId($product->getId())
            ->setOperationType($type);

        $this->changedProductResourceFactory
            ->create()
            ->save($changedProduct);
    }
}
