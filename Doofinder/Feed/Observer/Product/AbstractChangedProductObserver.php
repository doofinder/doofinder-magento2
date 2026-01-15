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
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
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

    /**
     * @var Configurable
     */
    private $configurableProductType;

    /**
     * @var []
     */
    private $visibilityAllowed;

    /**
     * AbstractChangedProductObserver constructor.
     *
     * @param StoreConfig $storeConfig
     * @param ChangedItemFactory $changedItemFactory
     * @param ChangedItemRepositoryInterface $changedItemRepository
     * @param LoggerInterface $logger
     * @param Configurable $configurableProductType
     */
    public function __construct(
        StoreConfig $storeConfig,
        ChangedItemFactory $changedItemFactory,
        ChangedItemRepositoryInterface $changedItemRepository,
        LoggerInterface $logger,
        Configurable $configurableProductType
    ) {
        $this->storeConfig                  = $storeConfig;
        $this->changedItemFactory           = $changedItemFactory;
        $this->changedItemRepository        = $changedItemRepository;
        $this->logger                       = $logger;
        $this->configurableProductType      = $configurableProductType;
        $this->visibilityAllowed            =  [Visibility::VISIBILITY_IN_SEARCH, visibility::VISIBILITY_BOTH];
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->storeConfig->isUpdateOnSave()) {
            try {
                $product = $observer->getEvent()->getProduct();
                $operationType = $this->getOperationType();
                $parentProducts = $this->configurableProductType->getParentIdsByChild($product->getId());
                
                if ($product->getStatus() == Status::STATUS_DISABLED) {
                    $this->setOperationType(ChangedItemInterface::OPERATION_TYPE_DELETE);
                } elseif (!in_array($product->getVisibility(), $this->visibilityAllowed)
                    && count($parentProducts) == 0) {
                    $this->setOperationType(ChangedItemInterface::OPERATION_TYPE_DELETE);
                } elseif ($product->getUpdatedAt() == $product->getCreatedAt() &&
                    $operationType == ChangedItemInterface::OPERATION_TYPE_UPDATE
                ) {
                    $this->setOperationType(ChangedItemInterface::OPERATION_TYPE_CREATE);
                } elseif ($product->getUpdatedAt() != $product->getCreatedAt() &&
                    $operationType == ChangedItemInterface::OPERATION_TYPE_CREATE
                ) {
                    $this->setOperationType(ChangedItemInterface::OPERATION_TYPE_UPDATE);
                }
                
                if ($product->getStore()->getId() == 0 ||
                    $this->getOperationType() == ChangedItemInterface::OPERATION_TYPE_DELETE
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

    /**
     * Registers the item by its type in the table doofinder_feed_changed_item, with the corresponding store
     *
     * @param ProductInterface $product
     * @param int $storeId
     */
    protected function registerChangedItemStore(ProductInterface $product, int $storeId)
    {
        $itemId = $product->getId();
        $operationType = $this->getOperationType();
        $isDeleteOperation = $operationType === ChangedItemInterface::OPERATION_TYPE_DELETE;
        $parentProducts = $this->configurableProductType->getParentIdsByChild($itemId);
        $isVariant = count($parentProducts) > 0;
        $isParent = $product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;

        /*
        If this is a parent product being deleted, verify it's actually disabled.
        Don't register parent if it's being saved due to a variant change.
         */
        if ($isParent && $isDeleteOperation && $product->getStatus() != Status::STATUS_DISABLED) {
            /*
            Parent product is not actually disabled, skip registration
            This prevents registering parent when only a variant was disabled
            */
            return;
        }

        $itemsToInsert = [$itemId];
        
        /*
        For non-delete operations: if product is a variant, register parent instead
        (When updating a variant, its parent is also updated, so we register the parent)
        */
        if ($isVariant && !$isDeleteOperation) {
            $itemsToInsert = $parentProducts;
        }
        
        /*
        For delete operations: only register the product itself
        - If variant is deleted: register only the variant (not the parent)
        - If parent is deleted: register only the parent (not variants, handled in Dooplugins)
        */
        foreach ($itemsToInsert as $itemToInsert) {
            $changedItem = $this->createChangedItem((int)$itemToInsert, $storeId);
            if (!$this->changedItemRepository->exists($changedItem)) {
                $this->changedItemRepository->save($changedItem);
            }
        }
    }

    /**
     * Create changed product
     *
     * @param int $itemId
     * @param int $storeId
     *
     * @return ChangedItem
     */
    protected function createChangedItem(int $itemId, int $storeId): ChangedItem
    {
        $changedItem = $this->changedItemFactory->create();
        $changedItem
            ->setItemId($itemId)
            ->setStoreId($storeId)
            ->setItemType(ItemType::PRODUCT)
            ->setOperationType($this->getOperationType());

        return $changedItem;
    }

    /**
     * @inheritdoc
     */
    abstract protected function getOperationType(): string;

    /**
     * @inheritdoc
     */
    abstract protected function setOperationType(string $operationType);
}
