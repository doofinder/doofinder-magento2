<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map\Update\Fetcher;

use Doofinder\Feed\Model\Indexer\Data\Map\Update\FetcherInterface;
use Doofinder\Feed\Model\ResourceModel\Index;
use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Category as CategoryFieldNameResolver;

/**
 * Class Category
 * The class responsible for providing category data for index
 */
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
     * {@inheritDoc}
     * @param array $documents
     * @param integer $storeId
     * @return void
     */
    public function process(array $documents, $storeId)
    {
        $this->processed = $this->index->getCategoryProductIndexData($storeId, array_keys($documents));

        foreach ($this->processed as $productId => $categoryIds) {
            unset($this->processed[$productId]);
            $this->processed[$productId][CategoryFieldNameResolver::ATTR_NAME] = array_keys($categoryIds);
        }
    }

    /**
     * @param integer $productId
     * @return array
     */
    public function get($productId)
    {
        return $this->processed[$productId] ?? [];
    }

    /**
     * {@inheritDoc}
     * @return void
     */
    public function clear()
    {
        $this->processed = [];
    }
}
