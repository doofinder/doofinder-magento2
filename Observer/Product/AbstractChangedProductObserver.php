<?php

declare(strict_types=1);

namespace Doofinder\Feed\Observer\Product;

use Doofinder\Feed\Api\ChangedItemRepositoryInterface;
use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedItem;
use Doofinder\Feed\Model\ChangedItem\ItemType;
use Doofinder\Feed\Model\ChangedItemFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractChangedProductObserver implements ObserverInterface
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var ChangedItemFactory
     */
    private $changedItemFactory;

    /**
     * @var ChangedItemRepositoryInterface
     */
    private $changedItemRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StoreConfig $storeConfig,
        ChangedItemFactory $changedItemFactory,
        ChangedItemRepositoryInterface $changedItemRepository,
        LoggerInterface $logger
    ) {
        $this->storeConfig                  = $storeConfig;
        $this->changedItemFactory           = $changedItemFactory;
        $this->changedItemRepository        = $changedItemRepository;
        $this->logger                       = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->storeConfig->isUpdateOnSave()) {
            try {
                /** @var ProductInterface $product */
                $product = $observer->getEvent()->getProduct();
                $operationType = $this->getOperationType();

                if ($product->getStatus() == Status::STATUS_DISABLED) {
                    $this->setOperationType(ChangedItemInterface::OPERATION_TYPE_DELETE);
                } else if (
                    $product->getUpdatedAt() == $product->getCreatedAt() &&
                    $operationType == ChangedItemInterface::OPERATION_TYPE_UPDATE
                ) {
                    $this->setOperationType(ChangedItemInterface::OPERATION_TYPE_CREATE);
                } else if (
                    $product->getUpdatedAt() != $product->getCreatedAt() && 
                    $operationType == ChangedItemInterface::OPERATION_TYPE_CREATE
                ) {
                    $this->setOperationType(ChangedItemInterface::OPERATION_TYPE_UPDATE);
                }
                
                if (
                    $product->getStore()->getId() == 0 
                    || $this->getOperationType() == ChangedItemInterface::OPERATION_TYPE_DELETE
                ) {

                    foreach ($this->storeConfig->getAllStores() as $store) {
                        $this->registerChangedItemStore($product, (int)$store->getId());
                    } 

                } else {
                    $this->registerChangedItemStore($product, (int)$product->getStore()->getId());
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    protected function registerChangedItemStore(ProductInterface $product, int $storeId)
    {
        $changedItem = $this->createChangedItem($product, $storeId);
        if (!$this->changedItemRepository->exists($changedItem)) {
            $this->changedItemRepository->save($changedItem);
        }
    }

    /**
     * Create changed product
     *
     * @param ProductInterface $product
     * @param int $storeId
     *
     * @return ChangedItem
     */
    protected function createChangedItem(ProductInterface $product, int $storeId): ChangedItem
    {
        $changedItem = $this->changedItemFactory->create();
        $changedItem
            ->setItemId((int)$product->getId())
            ->setStoreId($storeId)
            ->setItemType(ItemType::PRODUCT)
            ->setOperationType($this->getOperationType());

        return $changedItem;
    }

    abstract protected function getOperationType(): string;
    abstract protected function setOperationType(string $operationType);
}
