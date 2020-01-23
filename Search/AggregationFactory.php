<?php

namespace Doofinder\Feed\Search;

use Magento\Framework\Search\Response\BucketFactory;
use Magento\Framework\Search\Response\AggregationFactory as ResponseAggregationFactory;
use Magento\Framework\Search\Response\Aggregation\ValueFactory;
use Magento\Framework\Search\Response\Aggregation;
use Magento\Framework\Search\Response\Aggregation\Value;

/**
 * Class AggregationFactory
 * The class responsible for creating aggregations
 */
class AggregationFactory
{
    /**
     * @var BucketFactory
     */
    private $bucketFactory;

    /**
     * @var ResponseAggregationFactory
     */
    private $aggregationFactory;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * AggregationFactory constructor.
     * @param BucketFactory $bucketFactory
     * @param ResponseAggregationFactory $aggregationFactory
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        BucketFactory $bucketFactory,
        ResponseAggregationFactory $aggregationFactory,
        ValueFactory $valueFactory
    ) {
        $this->bucketFactory = $bucketFactory;
        $this->aggregationFactory = $aggregationFactory;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Create Aggregation instance
     *
     * @param array $rawAggregation
     * @return Aggregation
     */
    public function create(array $rawAggregation)
    {
        $buckets = [];
        foreach ($rawAggregation as $rawBucketName => $rawBucket) {
            $buckets[$rawBucketName] = $this->bucketFactory->create([
                'name' => $rawBucketName,
                'values' => $this->prepareValues($rawBucket)
            ]);
        }
        $aggregation = $this->aggregationFactory->create(['buckets' => $buckets]);
        return $aggregation;
    }

    /**
     * Prepare values list
     *
     * @param array $values
     * @return Value[]
     */
    private function prepareValues(array $values)
    {
        $valuesObjects = [];
        foreach ($values as $name => $value) {
            $valuesObjects[] = $this->valueFactory->create([
                'value' => $name,
                'metrics' => $value,
            ]);
        }
        return $valuesObjects;
    }
}
