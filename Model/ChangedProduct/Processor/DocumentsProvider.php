<?php

namespace Doofinder\Feed\Model\ChangedProduct\Processor;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection as ChangedProductCollection;
use Generator;

/**
 * Class DocumentsProvider
 * The class responsible for providing documents for indexer
 */
class DocumentsProvider
{
    /**
     * @var Full
     */
    private $fullAction;

    /**
     * DocumentsProvider constructor.
     * @param Full $fullAction
     */
    public function __construct(Full $fullAction)
    {
        $this->fullAction = $fullAction;
    }

    /**
     * @param ChangedProductCollection $collection
     * @return Generator
     */
    public function getDeleted(ChangedProductCollection $collection)
    {
        if ($collection->getSize() === 0) {
            return;
        }

        foreach ($collection as $item) {
            yield $item[ChangedProductResource::FIELD_PRODUCT_ID];
        }
    }

    /**
     * @param ChangedProductCollection $collection
     * @param integer $storeId
     * @return Generator
     */
    public function getUpdated(ChangedProductCollection $collection, $storeId)
    {
        $productIds = $collection->getColumnValues(ChangedProductResource::FIELD_PRODUCT_ID);
        return $this->fullAction->rebuildStoreIndex($storeId, $productIds);
    }
}
