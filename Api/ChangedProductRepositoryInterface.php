<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api;

use Doofinder\Feed\Api\Data\ChangedProductInterface;
use Doofinder\Feed\Api\Data\ChangedProductSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
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
     * Retrieve entity by attribute
     *
     * @param $value
     * @param string|null $field
     * @return ChangedProductInterface
     * @throws NoSuchEntityException
     */
    public function get($value, ?string $field): ChangedProductInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ChangedProductSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ChangedProductSearchResultsInterface;
}
