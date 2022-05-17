<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model;

use Doofinder\Feed\Api\ChangedProductRepositoryInterface;
use Doofinder\Feed\Api\Data\ChangedProductInterface;
use Doofinder\Feed\Api\Data\ChangedProductSearchResultsInterface;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResourceModel;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class ChangedProductRepository implements ChangedProductRepositoryInterface
{
    /**
     * @var ChangedProductResourceModel
     */
    private $resourceModel;

    /**
     * @var ChangedProductFactory
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
     * @var ChangedProductSearchResultsFactory
     */
    private $searchResultsFactory;

    public function __construct(
        ChangedProductResourceModel $resourceModel,
        ChangedProductFactory $entityFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ChangedProductSearchResultsFactory $searchResultsFactory
    ) {
        $this->resourceModel        = $resourceModel;
        $this->entityFactory        = $entityFactory;
        $this->collectionFactory    = $collectionFactory;
        $this->collectionProcessor  = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(ChangedProductInterface $entity): ChangedProductInterface
    {
        $this->resourceModel->save($entity);

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $entityId): ChangedProductInterface
    {
        return $this->get($entityId);
    }

    /**
     * @inheritDoc
     */
    public function get($value, ?string $field = null): ChangedProductInterface
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
    public function delete(ChangedProductInterface $entity): bool
    {
        $entityId = $entity->getId();
        try {
            $this->resourceModel->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Unable to remove entity %1', $entityId)
            );
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $entityId): bool
    {
        $entity = $this->getById($entityId);

        return $this->delete($entity);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ChangedProductSearchResultsInterface
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
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();
        $results = [];
        foreach ($collection as $model) {
            $results[$model->getId()] = $model;
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function getSingle(SearchCriteriaInterface $searchCriteria): ?ChangedProductInterface
    {
        $collection = $this->getList($searchCriteria)->getItems();

        return count($collection) ? $collection[0] : null;
    }
}
