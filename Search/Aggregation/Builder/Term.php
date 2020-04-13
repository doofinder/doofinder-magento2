<?php

namespace Doofinder\Feed\Search\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

/**
 * Class Term
 * Builder for term buckets
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
        if (!isset($queryResult['aggregations'][$bucket->getName()]['buckets'])) {
            return $values;
        }
        $buckets = $queryResult['aggregations'][$bucket->getName()]['buckets'];
        foreach ($buckets as $key => $resultBucket) {
            $values[$key] = [
                'value' => $key,
                'count' => $resultBucket
            ];
        }

        return $values;
    }
}
