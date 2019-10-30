<?php

namespace Doofinder\Feed\Model\Layer\Search;

use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Category;

/**
 * Class ItemCollectionProvider
 */
class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * ItemCollectionProvider constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritDoc}
     * @param Category $category
     * @return Collection
     */
    public function getCollection(Category $category)
    {
        return $this->collectionFactory->create();
    }
}
