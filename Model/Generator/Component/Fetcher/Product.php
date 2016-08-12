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
     * @var boolean
     */
    protected $_isStarted = null;

    /**
     * @var boolean
     */
    protected $_isDone = null;

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
        $collection = $this->getProductCollection();
        $products = $collection->load();

        // Check if fetcher is started and done
        $this->_isStarted = $this->getCurPage() === null || $this->getCurPage() === 1;
        $this->_isDone = $this->getCurPage() === null || $this->getCurPage() >= $collection->getLastPageNumber();

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

    /**
     * Check if the first item has been fetched
     *
     * @return boolean
     */
    public function isStarted()
    {
        return $this->_isStarted;
    }

    /**
     * Check if the last item has been fetched
     *
     * @return boolean
     */
    public function isDone()
    {
        return $this->_isDone;
    }
}
