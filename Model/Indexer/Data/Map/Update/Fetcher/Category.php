<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map\Update\Fetcher;

use Doofinder\Feed\Model\Indexer\Data\Map\Update\FetcherInterface;
use Doofinder\Feed\Model\ResourceModel\Index;
use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Category as CategoryFieldNameResolver;
use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\CategoryPosition as CategoryPositionNameResolver;

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
     * @var CategoryPositionNameResolver
     */
    private $catPosNameResolver;

    /**
     * @var array|null
     */
    private $processed;

    /**
     * Category constructor.
     * @param Index $index
     * @param CategoryPositionNameResolver $catPosNameResolver
     */
    public function __construct(
        Index $index,
        CategoryPositionNameResolver $catPosNameResolver
    ) {
        $this->index = $index;
        $this->catPosNameResolver = $catPosNameResolver;
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
            foreach ($categoryIds as $categoryId => $categoryPosition) {
                $this->processed[$productId][CategoryFieldNameResolver::ATTR_NAME][] = (string) $categoryId;
                $posName = $this->catPosNameResolver->getFiledName($categoryId);
                $this->processed[$productId][$posName] = (float) $categoryPosition;
            }
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
