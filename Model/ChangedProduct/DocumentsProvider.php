<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\ChangedProduct;

use Doofinder\Feed\Api\Data\ChangedProductInterface;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection as ChangedProductCollection;
use Generator;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full;

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
    public function getDeleted(ChangedProductCollection $collection): Generator
    {
        if ($collection->getSize() === 0) {
            yield [];
        }

        foreach ($collection as $item) {
            yield $item[ChangedProductInterface::PRODUCT_ID];
        }
    }

    /**
     * @param ChangedProductCollection $collection
     * @param integer $storeId
     * @return Generator
     */
    public function getUpdated(ChangedProductCollection $collection, int $storeId): Generator
    {
        $productIds = $collection->getColumnValues(ChangedProductInterface::PRODUCT_ID);

        return $this->fullAction->rebuildStoreIndex($storeId, $productIds);
    }
}
