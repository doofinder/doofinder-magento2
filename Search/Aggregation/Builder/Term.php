<?php

namespace Doofinder\Feed\Search\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

/**
 * Class Term
 * The class responsible for preparing bucket from Doofinder result.
 */
class Term
{
    /**
     * @param DataProviderInterface $dataProvider
     * @param array $dimensions
     * @param RequestBucketInterface $bucket
     * @param array $queryResult
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
     */
    public function build(
        DataProviderInterface $dataProvider,
        array $dimensions,
        RequestBucketInterface $bucket,
        array $queryResult
    ) {
        // phpcs:enable
        $values = [];
        if (!isset($queryResult['aggregation'][$bucket->getField()])) {
            return $values;
        }
        $facet = $queryResult['aggregation'][$bucket->getField()];

        if (empty($facet['terms']['buckets'])) {
            return $values;
        }

        foreach ($facet['terms']['buckets'] as $facetBucket) {
            $values[$facetBucket['key']] = [
                'value' => $facetBucket['key'],
                'count' => $facetBucket['doc_count']
            ];
        }
        return $values;
    }
}
