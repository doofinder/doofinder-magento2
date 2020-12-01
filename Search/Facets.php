<?php

namespace Doofinder\Feed\Search;

use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Price as PriceNameResolver;
use Magento\Framework\Search\Request\BucketInterface;

/**
 * Class Facets
 * The class responsible for providing Magento buckets translated into Doofinder Facets
 */
class Facets
{
    /**
     * @var PriceNameResolver
     */
    private $priceNameResolver;

    /**
     * Facets constructor.
     * @param PriceNameResolver $priceNameResolver
     */
    public function __construct(PriceNameResolver $priceNameResolver)
    {
        $this->priceNameResolver = $priceNameResolver;
    }

    /**
     * @param BucketInterface[] $buckets
     * @return array
     */
    public function get(array $buckets)
    {
        $buckets = array_values($buckets);
        $facets = [];

        foreach ($buckets as $key => $bucket) {
            $fieldType = 'size';
            $fieldTypeValue = 50;
            $fieldName = $bucket->getField();
            if ($fieldName == 'price') {
                $fieldName = $this->priceNameResolver->getFiledName();
                $fieldType = 'type';
                $fieldTypeValue = 'range';
            }
            $facets[$key]['field'] = $fieldName;
            $facets[$key][$fieldType] = $fieldTypeValue;
        }

        return $facets;
    }
}
