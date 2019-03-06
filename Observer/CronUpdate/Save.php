<?php

namespace Doofinder\Feed\Observer\CronUpdate;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedProductFactory;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Doofinder\Feed\Model\ResourceModel\ChangedProductFactory as ChangedProductResourceFactory;

/**
 * This class is responsible for leaving a trace of updated product to be later synchronized with Doofinder.
 */
class Save implements ObserverInterface
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Model\ChangedProductFactory $changedProductFactory
     */
    private $changedProductFactory;

    /**
     * @var \Doofinder\Feed\Model\ResourceModel\ChangedProductFactory $changedProductResourceFactory
     */
    private $changedProductResourceFactory;

    /**
     * A constructor.
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Model\ChangedProductFactory $changedProductFactory
     * @param \Doofinder\Feed\Model\ResourceModel\ChangedProductFactory $changedProductResourceFactory
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
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->storeConfig->isCronUpdatesEnabled()) {
            return;
        }

        $product = $observer->getDataObject();

        if (!$product) {
            return;
        }

        $changedProduct = $this->changedProductFactory
            ->create()
            ->setProductEntityId($product->getId())
            ->setOperationType(ChangedProductResource::OPERATION_UPDATE);

        $this->changedProductResourceFactory
            ->create()
            ->save($changedProduct);
    }
}
