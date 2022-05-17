<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model\Indexer\Data\Map\Update\Fetcher;

use Doofinder\Feed\Api\Data\FetcherInterface;
use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Price as PriceNameFieldResolver;
use Doofinder\Feed\Model\ResourceModel\Index;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Zend_Db_Select_Exception;

class Price implements FetcherInterface
{
    /**
     * @var Index
     */
    private $index;

    /**
     * @var PriceNameFieldResolver
     */
    private $priceNameResolver;

    /**
     * @var array
     */
    private $processed;

    /**
     * Price constructor.
     * @param Index $index
     * @param PriceNameFieldResolver $priceNameResolver
     */
    public function __construct(
        Index $index,
        PriceNameFieldResolver $priceNameResolver
    ) {
        $this->index = $index;
        $this->priceNameResolver = $priceNameResolver;
    }

    /**
     * @param array $documents
     * @param int $storeId
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Zend_Db_Select_Exception
     */
    public function process(array $documents, int $storeId)
    {
        $priceIndexData = $this->index->getPriceIndexData(array_keys($documents), $storeId);
        foreach ($priceIndexData as $productId => $customerGroupPrices) {
            foreach ($customerGroupPrices as $customerGroupId => $price) {
                $fieldName = $this->priceNameResolver->getFieldName(['customer_group_id' => $customerGroupId]);
                $this->processed[$productId][$fieldName] = $price;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(int $productId): array
    {
        return $this->processed[$productId] ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->processed = [];
    }
}
