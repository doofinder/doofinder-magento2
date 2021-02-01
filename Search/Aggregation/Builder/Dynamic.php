<?php
namespace Doofinder\Feed\Search\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\Algorithm\Repository;
use Magento\Framework\Search\Dynamic\EntityStorageFactory;

/**
 * Class Dynamic
 * Builder for dynamic buckets
 */
class Dynamic
{
    /**
     * @var Repository
     */
    private $algorithmRepository;

    /**
     * @var EntityStorageFactory
     */
    private $entityStorageFactory;

    /**
     * Dynamic constructor.
     * @param Repository $algorithmRepository
     * @param EntityStorageFactory $entityStorageFactory
     */
    public function __construct(
        Repository $algorithmRepository,
        EntityStorageFactory $entityStorageFactory
    ) {
        $this->algorithmRepository = $algorithmRepository;
        $this->entityStorageFactory = $entityStorageFactory;
    }

    /**
     * @param DataProviderInterface $dataProvider
     * @param array $dimensions
     * @param RequestBucketInterface $bucket
     * @param array $queryResult
     * @return array
     */
    public function build(
        DataProviderInterface $dataProvider,
        array $dimensions,
        RequestBucketInterface $bucket,
        array $queryResult
    ) {
        /** @var DynamicBucket $bucket */
        $algorithm = $this->algorithmRepository->get($bucket->getMethod(), ['dataProvider' => $dataProvider]);

        if (!isset($queryResult['ids'])) {
            return [];
        }
        $ids = array_values($queryResult['ids']);
        $entityStorage = $this->entityStorageFactory->create($ids);
        $data = $algorithm->getItems($bucket, $dimensions, $entityStorage);

        $resultData = $this->prepareData($data);
        return $resultData;
    }

    /**
     * Prepare result data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data)
    {
        $resultData = [];
        foreach ($data as $value) {
            $from = is_numeric($value['from']) ? $value['from'] : '';
            $to = is_numeric($value['to']) ? $value['to'] : '';
            unset($value['from'], $value['to']);

            $rangeName = sprintf('%s_%s', $from, $to);
            // phpcs:disable Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
            $resultData[$rangeName] = array_merge(['value' => $rangeName], $value);
            // phpcs:enable
        }
        return $resultData;
    }
}
