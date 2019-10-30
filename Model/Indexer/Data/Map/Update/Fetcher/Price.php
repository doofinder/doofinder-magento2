<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map\Update\Fetcher;

use Doofinder\Feed\Model\Indexer\Data\Map\Update\FetcherInterface;
use Doofinder\Feed\Model\ResourceModel\Index;
use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Price as PriceNameFieldResolver;

/**
 * Class Price
 * The class responsible for providing price data to index
 */
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
     * {@inheritDoc}
     * @param array $documents
     * @param integer $storeId
     * @return void
     */
    public function process(array $documents, $storeId)
    {
        $priceIndexData = $this->index->getPriceIndexData(
            array_keys($documents),
            $storeId
        );

        foreach ($priceIndexData as $productId => $customerGroupPrices) {
            foreach ($customerGroupPrices as $customerGroupId => $price) {
                $fieldName = $this->priceNameResolver->getFiledName(['customer_group_id' => $customerGroupId]);
                $this->processed[$productId][$fieldName] = $price;
            }
        }
    }

    /**
     * {@inheritDoc}
     * @param integer $productId
     * @return array
     */
    public function get($productId)
    {
        return $this->processed[$productId] ?? [];
    }

    /**
     * {@inheritDoc}
     * @return void
     */
    public function clear()
    {
        $this->processed = [];
    }
}
