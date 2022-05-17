<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api;

use Doofinder\Feed\Api\Data\ChangedProductInterface;
use Doofinder\Feed\Api\Data\ChangedProductSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ChangedProductRepositoryInterface
{
    /**
     * Save entity
     *
     * @param ChangedProductInterface $entity
     * @return ChangedProductInterface
     * @throws AlreadyExistsException
     */
    public function save(ChangedProductInterface $entity): ChangedProductInterface;

    /**
     * Retrieve entity by id
     *
     * @param int $entityId
     * @return ChangedProductInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): ChangedProductInterface;

    /**
     * Retrieve entity by attribute
     *
     * @param $value
     * @param string|null $field
     * @return ChangedProductInterface
     * @throws NoSuchEntityException
     */
    public function get($value, ?string $field): ChangedProductInterface;

    /**
     * @param ChangedProductInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ChangedProductInterface $entity): bool;

    /**
     * Delete entity by ID.
     *
     * @param int $entityId
     * @return boolean
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $entityId): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ChangedProductSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ChangedProductSearchResultsInterface;

    /**
     * @return ChangedProductInterface[]
     */
    public function getAll(): array;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ChangedProductInterface|null
     */
    public function getSingle(SearchCriteriaInterface $searchCriteria): ?ChangedProductInterface;
}
