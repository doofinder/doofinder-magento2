<?php

namespace Doofinder\Feed\Model\Generator\Component\Fetcher;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\Fetcher;

class Product extends Component implements Fetcher
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory = null;

    /**
     * @var \Doofinder\Feed\Model\Generator\ItemFactory
     */
    protected $_generatorItemFactory = null;

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Doofinder\Feed\Model\Generator\ItemFactory $generatorItemFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_generatorItemFactory = $generatorItemFactory;
        parent::__construct($logger, $data);
    }

    /**
     * Fetch products
     *
     * @return Item[]
     */
    public function fetch()
    {
        $products = $this->getProductCollection()->load();

        $items = array();
        foreach ($products as $product) {
            $items[] = $this->createItem($product);
        }

        return $items;
    }

    /**
     * Get product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addStoreFilter()
            ->addAttributeToSelect('*')
            ->addAttributeToSort('id', 'asc');

        if ($pageSize = $this->getPageSize()) {
            $collection->setPageSize($pageSize);
        }

        if ($pageNum = $this->getCurPage()) {
            $collection->setCurPage($pageNum);
        }

        return $collection;
    }

    /**
     * Create item from product
     *
     * @param \Magento\Catalog\Model\Product
     */
    protected function createItem(\Magento\Catalog\Model\Product $product)
    {
        $item = $this->_generatorItemFactory->create();
        $item->setContext($product);

        return $item;
    }
}
