<?php

namespace Doofinder\Feed\Search\Aggregation;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\BucketBuilderInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

/**
 * Class Builder
 * The class responsible for building aggregations for the search request
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Builder
{
    /**
     * @var array
     */
    private $dataProviderContainer;

    /**
     * @var array
     */
    private $aggregationContainer;

    /**
     * Builder constructor.
     * @param array $dataProviderContainer
     * @param array $aggregationContainer
     */
    public function __construct(
        array $dataProviderContainer,
        array $aggregationContainer
    ) {
        $this->dataProviderContainer = $dataProviderContainer;
        $this->aggregationContainer = $aggregationContainer;
    }

    /**
     * Builds aggregations from the search request.
     *
     * This method iterates through buckets and builds all aggregations one by one, passing buckets and relative
     * data into bucket aggregation builders which are responsible for aggregation calculation.
     *
     * @param RequestInterface $request
     * @param array $queryResult
     * @return array
     */
    public function build(RequestInterface $request, array $queryResult)
    {
        $aggregations = [];
        $buckets = $request->getAggregation();

        $dataProvider = $this->dataProviderContainer[$request->getIndex()];

        foreach ($buckets as $bucket) {
            $bucketAggregationBuilder = $this->aggregationContainer[$bucket->getType()];
            $aggregations[$bucket->getName()] = $bucketAggregationBuilder->build(
                $dataProvider,
                $request->getDimensions(),
                $bucket,
                $queryResult
            );
        }

        return $aggregations;
    }
}
