<?php

declare(strict_types=1);

namespace Doofinder\Feed\Observer\Category;

use Doofinder\Feed\Api\ChangedItemRepositoryInterface;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedItem;
use Doofinder\Feed\Model\ChangedItemFactory;
use Doofinder\Feed\Model\ChangedItem\ItemType;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractChangedCategoryObserver implements ObserverInterface
{

    /**
     * @var StoreConfig
     */
    protected $storeConfig;

    /**
     * @var ChangedItemFactory
     */
    protected $changedItemFactory;

    /**
     * @var ChangedItem
     */
    protected $changedItem;

    /**
     * @var ChangedItemRepositoryInterface
     */
    protected $changedItemRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AbstractChangedCategoryObserver constructor.
     *
     * @param StoreConfig $storeConfig
     * @param ChangedItemFactory $changedItemFactory
     * @param ChangedItemRepositoryInterface $changedItemRepository
     * @param LoggerInterface $logger
     */
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
    abstract public function execute(Observer $observer);

    /**
     * Saves the category into changed item table if necessary
     * 
     * @param CategoryInterface $category
     * @param int $storeId
     */
    protected function registerChangedItemStore(CategoryInterface $category, int $storeId)
    {
        $changedPage = $this->createChangedItem($category, $storeId);
        if (!$this->changedItemRepository->exists($changedPage, ItemType::CATEGORY)) {
            $this->changedItemRepository->save($changedPage);
        }
    }

    /**
     * Create changed product
     *
     * @param CategoryInterface $category
     * @param int $storeId
     *
     * @return ChangedItem
     */
    protected function createChangedItem(CategoryInterface $category, int $storeId): ChangedItem
    {
        $changedItem = $this->changedItemFactory->create();
        $changedItem
            ->setItemId((int)$category->getId())
            ->setStoreId($storeId)
            ->setItemType(ItemType::CATEGORY)
            ->setOperationType($this->getOperationType($category));

        return $changedItem;
    }

    /**
     * Gets operation type
     * 
     * @param CategoryInterface $category
     * 
     * @return string
     */
    abstract protected function getOperationType(CategoryInterface $category): string;
}
