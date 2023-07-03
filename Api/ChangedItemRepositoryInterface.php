<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api;

use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Doofinder\Feed\Api\Data\ChangedItemSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ChangedItemRepositoryInterface
{
    /**
     * Save entity
     *
     * @param ChangedItemInterface $entity
     * @return ChangedItemInterface
     * @throws AlreadyExistsException
     */
    public function save(ChangedItemInterface $entity): ChangedItemInterface;

    /**
     * Retrieve entity by attribute
     *
     * @param $value
     * @param string|null $field
     * @return ChangedItemInterface
     * @throws NoSuchEntityException
     */
    public function get($value, ?string $field): ChangedItemInterface;

    /**
     * Gets entity's list by search criteria
     * 
     * @param SearchCriteriaInterface $searchCriteria
     * @return ChangedItemSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ChangedItemSearchResultsInterface;

    /**
     * Determines whether this item exists or not
     * 
     * @param ChangedItemInterface $changedItem
     * @return bool
     */
    public function exists(ChangedItemInterface $changedItem): bool;
}
