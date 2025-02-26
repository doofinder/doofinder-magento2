<?php

declare(strict_types=1);

namespace Doofinder\Feed\Observer\Page;

use Doofinder\Feed\Api\ChangedItemRepositoryInterface;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedItem;
use Doofinder\Feed\Model\ChangedItemFactory;
use Doofinder\Feed\Model\ChangedItem\ItemType;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractChangedCmsPageObserver implements ObserverInterface
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
     * AbstractChangedCmsPageObserver constructor.
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
     * Saves the page into changed item table if necessary
     *
     * @param CategoryInterface $category
     * @param int $storeId
     */
    protected function registerChangedItemStore(PageInterface $page, int $storeId)
    {
        $changedPage = $this->createChangedItem($page, $storeId);
        if (!$this->changedItemRepository->exists($changedPage, ItemType::PAGE)) {
            $this->changedItemRepository->save($changedPage);
        }
    }

    /**
     * Create changed product
     *
     * @param PageInterface $page
     * @param int $storeId
     *
     * @return ChangedItem
     */
    protected function createChangedItem(PageInterface $page, int $storeId): ChangedItem
    {
        $changedItem = $this->changedItemFactory->create();
        $changedItem
            ->setItemId((int)$page->getId())
            ->setStoreId($storeId)
            ->setItemType(ItemType::PAGE)
            ->setOperationType($this->getOperationType($page));

        return $changedItem;
    }

    /**
     * Gets operation type
     *
     * @param PageInterface $page
     *
     * @return string
     */
    abstract protected function getOperationType(PageInterface $page): string;
}
