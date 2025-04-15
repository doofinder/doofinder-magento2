<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model;

use Doofinder\Feed\Api\ChangedItemRepositoryInterface;
use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Doofinder\Feed\Api\Data\ChangedItemSearchResultsInterface;
use Doofinder\Feed\Model\ResourceModel\ChangedItem as ChangedItemResourceModel;
use Doofinder\Feed\Model\ResourceModel\ChangedItem\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ChangedItemRepository implements ChangedItemRepositoryInterface
{
    /**
     * @var ChangedItemResourceModel
     */
    private $resourceModel;

    /**
     * @var ChangedItemFactory
     */
    private $entityFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ChangedItemSearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * ChangedItemRepository constructor.
     *
     * Initializes dependencies for handling ChangedItem entities.
     *
     * @param ChangedItemResourceModel         $resourceModel
     * @param ChangedItemFactory               $entityFactory
     * @param CollectionFactory                $collectionFactory
     * @param CollectionProcessorInterface     $collectionProcessor
     * @param SearchCriteriaBuilderFactory     $searchCriteriaBuilderFactory
     * @param ChangedItemSearchResultsFactory  $searchResultsFactory
     */
    public function __construct(
        ChangedItemResourceModel $resourceModel,
        ChangedItemFactory $entityFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        ChangedItemSearchResultsFactory $searchResultsFactory
    ) {
        $this->resourceModel                = $resourceModel;
        $this->entityFactory                = $entityFactory;
        $this->collectionFactory            = $collectionFactory;
        $this->collectionProcessor          = $collectionProcessor;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->searchResultsFactory         = $searchResultsFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(ChangedItemInterface $entity): ChangedItemInterface
    {
        $this->resourceModel->save($entity);

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function get($value, ?string $field = null): ChangedItemInterface
    {
        $entity = $this->entityFactory->create();
        $this->resourceModel->load($entity, $value, $field);

        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Unable to find entity'));
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ChangedItemSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $searchResults = $this->searchResultsFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function exists($changedItem): bool
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder
            ->addFilter('item_id', $changedItem->getItemId())
            ->addFilter('store_id', $changedItem->getStoreId())
            ->addFilter('operation_type', $changedItem->getOperationType())
            ->addFilter('item_type', $changedItem->getItemType());
        $searchCriteria = $searchCriteriaBuilder->create();
        $changedItemList = $this->getList($searchCriteria);

        return (bool)$changedItemList->getTotalCount();
    }
}
