<?php

namespace Doofinder\Feed\Search\Dynamic;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Framework\App\ScopeResolverInterface;
use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Price as PriceNameResolver;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Dynamic\IntervalInterface;
use Doofinder\Feed\Search\Cache;

/**
 * Class DataProvider
 * Dynamic prices data provider
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var Range
     */
    private $range;

    /**
     * @var IntervalFactory
     */
    private $intervalFactory;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var PriceNameResolver
     */
    private $priceNameResolver;

    /**
     * DataProvider constructor.
     * @param Range $range
     * @param IntervalFactory $intervalFactory
     * @param ScopeResolverInterface $scopeResolver
     * @param Cache $cache
     * @param PriceNameResolver $priceNameResolver
     */
    public function __construct(
        Range $range,
        IntervalFactory $intervalFactory,
        ScopeResolverInterface $scopeResolver,
        Cache $cache,
        PriceNameResolver $priceNameResolver
    ) {
        $this->range = $range;
        $this->intervalFactory = $intervalFactory;
        $this->scopeResolver = $scopeResolver;
        $this->cache = $cache;
        $this->priceNameResolver = $priceNameResolver;
    }

    /**
     * {@inheritDoc}
     * @return array|integer
     */
    public function getRange()
    {
        return $this->range->getPriceRange();
    }

    /**
     * Filter response by product ids
     * @param array $productIds
     * @return array
     */
    private function getFilteredResponse(array $productIds)
    {
        return array_filter($this->cache->getResponse(), function ($product) use ($productIds) {
            return in_array($product['id'], $productIds);
        });
    }

    /**
     * {@inheritDoc}
     * @param EntityStorage $entityStorage
     * @return array
     */
    public function getAggregations(EntityStorage $entityStorage)
    {
        $aggregations = [
            'count' => 0,
            'max' => 0,
            'min' => 0,
            'std' => 0,
        ];

        $res = $this->getFilteredResponse($entityStorage->getSource());

        if (empty($res)) {
            return $aggregations;
        }
        $priceFieldName = $this->priceNameResolver->getFiledName();
        $prices = [];
        foreach ($res as $rawDocument) {
            $prices[] = $rawDocument[$priceFieldName];
        }

        if (!$prices) {
            return $aggregations;
        }

        $deviation = $this->getStatsStandardDeviation($prices);
        $min = min($prices);
        $max = max($prices);
        $count = count($res);

        $aggregations = [
            'count' => $count,
            'max' => $max,
            'min' => $min,
            'std' => $deviation,
        ];

        return $aggregations;
    }

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param EntityStorage $entityStorage
     * @return IntervalInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceBeforeLastUsed
     */
    public function getInterval(
        BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    ) {
        // phpcs:enable
        $entityIds = $entityStorage->getSource();
        $fieldName = $this->priceNameResolver->getFiledName();
        $dimension = current($dimensions);
        $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();

        return $this->intervalFactory->create(
            [
                'entityIds' => $entityIds,
                'storeId' => $storeId,
                'fieldName' => $fieldName,
            ]
        );
    }

    /**
     * {@inheritDoc}
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param integer $range
     * @param EntityStorage $entityStorage
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceBeforeLastUsed
     */
    public function getAggregation(
        BucketInterface $bucket,
        array $dimensions,
        $range,
        EntityStorage $entityStorage
    ) {
        // phpcs:enable
        $result = [];

        $res = $this->getFilteredResponse($entityStorage->getSource());
        if (!$res) {
            return $result;
        }

        $priceFieldName = $this->priceNameResolver->getFiledName();
        $prices = [];
        foreach ($res as $rawDocument) {
            $prices[] = $rawDocument[$priceFieldName];
        }

        if (!$prices) {
            return $result;
        }

        $counts = [];
        foreach ($prices as $val) {
            $idx = ((int) ($val / $range)) * $range;
            if (!isset($counts[$idx])) {
                $counts[$idx] = 0;
            }
            $counts[$idx]++;
        }

        ksort($counts);

        $buckets = [];
        foreach ($counts as $key => $value) {
            $buckets[] = [
                'key' => $key,
                'doc_count' => $value
            ];
        }

        foreach ($buckets as $bucket) {
            $key = (int)($bucket['key'] / $range + 1);
            $result[$key] = $bucket['doc_count'];
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param integer $range
     * @param array $dbRanges
     * @return array
     */
    public function prepareData($range, array $dbRanges)
    {
        $data = [];
        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];
            foreach ($dbRanges as $index => $count) {
                $fromPrice = $index == 1 ? '' : ($index - 1) * $range;
                $toPrice = $index == $lastIndex ? '' : $index * $range;
                $data[] = [
                    'from' => $fromPrice,
                    'to' => $toPrice,
                    'count' => $count,
                ];
            }
        }

        return $data;
    }

    /**
     * @param array $prices
     * @param boolean $sample
     * @return boolean|float
     */
    private function getStatsStandardDeviation(array $prices, $sample = false)
    {
        $count = count($prices);
        if ($count === 0) {
            return false;
        }
        if ($sample && $count === 1) {
            return false;
        }
        $mean = array_sum($prices) / $count;
        $carry = 0.0;
        foreach ($prices as $val) {
            $d = ((double) $val) - $mean;
            $carry += $d * $d;
        };
        if ($sample) {
            --$count;
        }
        return sqrt($carry / $count);
    }
}
