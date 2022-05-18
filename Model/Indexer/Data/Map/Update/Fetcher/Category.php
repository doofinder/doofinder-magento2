<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Indexer\Data\Map\Update\Fetcher;

use Doofinder\Feed\Api\Data\FetcherInterface;
use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Category as CategoryFieldNameResolver;
use Doofinder\Feed\Model\ResourceModel\Index;

class Category implements FetcherInterface
{
    /**
     * @var Index
     */
    private $index;

    /**
     * @var array|null
     */
    private $processed;

    /**
     * Category constructor.
     * @param Index $index
     */
    public function __construct(
        Index $index
    ) {
        $this->index = $index;
    }

    /**
     * @inheritDoc
     */
    public function process(array $documents, int $storeId)
    {
        $this->processed = $this->index->getCategoryProductIndexData($storeId, array_keys($documents));

        foreach ($this->processed as $productId => $categoryIds) {
            unset($this->processed[$productId]);
            $this->processed[$productId][CategoryFieldNameResolver::ATTR_NAME] = array_keys($categoryIds);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(int $productId): array
    {
        return $this->processed[$productId] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->processed = [];
    }
}
