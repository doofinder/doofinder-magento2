<?php

namespace Doofinder\Feed\Model\ChangedProduct\Processor;

use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResourceModel;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\CollectionFactory as ChangedProductCollectionFactory;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection as ChangedProductCollection;
use InvalidArgumentException;

/**
 * Class CollectionProvider
 * The class responsible for providing Collection with Changed Products
 */
class CollectionProvider
{
    /**
     * @var ChangedProductCollectionFactory
     */
    private $collectionFactory;

    /**
     * CollectionProvider constructor.
     * @param ChangedProductCollectionFactory $collectionFactory
     */
    public function __construct(ChangedProductCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param string $type
     * @param string $storeCode
     * @return ChangedProductCollection
     * @throws InvalidArgumentException If missing type or store code.
     */
    public function get($type, $storeCode)
    {
        if (!$type || !$storeCode) {
            throw new InvalidArgumentException(
                'Missing required arguments: type or store code. Cannot filter collection'
            );
        }
        $collection = $this->collectionFactory->create();

        switch ($type) {
            case ChangedProductResourceModel::OPERATION_DELETE:
                $collection->filterDeleted($storeCode);
                break;
            case ChangedProductResourceModel::OPERATION_UPDATE:
                $collection->filterUpdated($storeCode);
                break;
        }
        return $collection;
    }
}
