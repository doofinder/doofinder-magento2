<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map;

use Doofinder\Feed\Model\Indexer\Data\MapInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Class Delete
 * The class responsible for providing products that should be deleted in the index
 */
class Delete implements MapInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * Delete constructor.
     * @param CollectionFactory $collectionFactory
     * @param Visibility $visibility
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Visibility $visibility
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->visibility = $visibility;
    }

    /**
     * {@inheritDoc}
     * @param array $documents
     * @param integer $scopeId
     * @return array
     */
    public function map(array $documents, $scopeId)
    {
        $products = $this->getProducts(array_values($documents), $scopeId);
        return array_diff($documents, $products);
    }

    /**
     * @param array $productIds
     * @param integer $scopeId
     * @return array
     */
    private function getProducts(array $productIds, $scopeId)
    {
        if (empty($productIds)) {
            return [];
        }
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
        $collection->setStoreId($scopeId);
        $collection->setVisibility($this->visibility->getVisibleInSiteIds());

        $collectionResult = $collection->toArray(['entity_id']);

        $products = [];
        foreach ($collectionResult as $item) {
            $products[] = $item['entity_id'];
        }

        return $products;
    }
}
