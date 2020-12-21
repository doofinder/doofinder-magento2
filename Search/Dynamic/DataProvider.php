<?php

namespace Doofinder\Feed\Search\Dynamic;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Dynamic\IntervalInterface;

/**
 * Class DataProvider
 * The class responsible for providing dynamic prices
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
     * @var DataProvider\SelectProvider
     */
    private $selectProvider;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * DataProvider constructor.
     * @param Range $range
     * @param IntervalFactory $intervalFactory
     * @param DataProvider\SelectProvider $selectProvider
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Range $range,
        IntervalFactory $intervalFactory,
        DataProvider\SelectProvider $selectProvider,
        ResourceConnection $resourceConnection
    ) {
        $this->range = $range;
        $this->intervalFactory = $intervalFactory;
        $this->selectProvider = $selectProvider;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return mixed
     */
    public function getRange()
    {
        return $this->range->getPriceRange();
    }

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param EntityStorage $entityStorage
     * @return IntervalInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getInterval(
        BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    ) {
        return $this->intervalFactory->create();
    }

    /**
     * @param EntityStorage $entityStorage
     * @return array
     */
    public function getAggregations(EntityStorage $entityStorage)
    {
        $aggregation = [
            'count' => 'count(main_table.entity_id)',
            'max' => 'MAX(min_price)',
            'min' => 'MIN(min_price)',
            'std' => 'STDDEV_SAMP(min_price)',
        ];

        $select = $this->selectProvider->get($entityStorage);
        $select->columns($aggregation);
        return $this->resourceConnection->getConnection()->fetchRow($select);
    }

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param integer $range
     * @param EntityStorage $entityStorage
     * @return array
     */
    public function getAggregation(
        BucketInterface $bucket,
        array $dimensions,
        $range,
        EntityStorage $entityStorage
    ) {
        $select = $this->selectProvider->get($entityStorage);
        $connection = $this->resourceConnection->getConnection();
        $rangeExpr = new \Zend_Db_Expr(
            $connection->getIfNullSql(
                $connection->quoteInto('FLOOR(min_price / ? ) + 1', $range),
                1
            )
        );

        $select
            ->columns(['range' => $rangeExpr])
            ->columns(['metrix' => 'COUNT(*)'])
            ->group('range')
            ->order('range');
        return $connection->fetchPairs($select);
    }

    /**
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
}
