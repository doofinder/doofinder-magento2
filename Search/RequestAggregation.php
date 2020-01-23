<?php

namespace Doofinder\Feed\Search;

use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Price as PriceFieldName;

/**
 * Class RequestAggregation
 * The class responsible for creating bucket aggregations for Layered Navigation
 */
class RequestAggregation
{
    /**
     * @var PriceFieldName
     */
    private $priceFieldName;

    /**
     * RequestAggregation constructor.
     * @param PriceFieldName $priceFieldName
     */
    public function __construct(PriceFieldName $priceFieldName)
    {
        $this->priceFieldName = $priceFieldName;
    }

    /**
     * @param array $buckets
     * @param array $rawResponse
     * @return array
     */
    public function get(array $buckets, array $rawResponse)
    {
        $aggregatesBuckets = [];
        foreach ($buckets as $bucket) {
            $attribute = $bucket->getField();
            if ($attribute == 'price') {
                $attribute = $this->priceFieldName->getFiledName();
            }

            $bucketName = $bucket->getName();

            $aggregatesBuckets[$bucketName]['buckets'] = [];
            foreach ($rawResponse as $product) {
                if (isset($product[$attribute]) && !empty($product[$attribute])) {
                    $attributeValue = $product[$attribute];
                    if (!is_array($attributeValue)) {
                        $attributeValue = [$attributeValue];
                    }

                    foreach ($attributeValue as $value) {
                        if (!isset($aggregatesBuckets[$bucketName]['buckets'][$value])) {
                            $aggregatesBuckets[$bucketName]['buckets'][$value] = 1;
                            continue;
                        }
                        $aggregatesBuckets[$bucketName]['buckets'][$value]++;
                    }
                }
            }
        }
        return $aggregatesBuckets;
    }
}
