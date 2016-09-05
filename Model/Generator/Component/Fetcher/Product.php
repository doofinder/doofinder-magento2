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
     * @var int
     */
    protected $_lastProcessedEntityId = null;

    /**
     * Amount of all products left in current fetch
     *
     * @var int
     */
    protected $_itemsLeftCount = null;

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
        $collection = $this->getProductCollection($this->getLimit(), $this->getOffset());
        $products = $collection->load();

        // Check if fetcher is started and done
        $this->_isStarted = !$this->getOffset();
        $this->_isDone = !$this->getLimit() || $this->getLimit() >= $collection->getSize();

        $items = array();
        foreach ($products as $product) {
            $items[] = $this->createItem($product);
        }

        // Set the last processed entity id
        $this->_lastProcessedEntityId = $this->getOffset();
        if ($collection->getSize()) {
            $this->_lastProcessedEntityId = $collection->getLastItem()->getEntityId();
        }

        // Set fetched size
        $this->_itemsLeftCount = $this->getLimit() ? max(0, $collection->getSize() - $this->getLimit()) : 0;

        return $items;
    }

    /**
     * Get product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getProductCollection($limit = null, $offset = null)
    {
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addStoreFilter()
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', [
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
            ])
            ->addAttributeToSort('id', 'asc');

        if ($limit) {
            $collection->setPageSize($limit);
        }

        if ($offset) {
            $collection->addAttributeToFilter('entity_id', ['gt' => $offset]);
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

    /**
     * Get last processed entity id
     *
     * @return int
     */
    public function getLastProcessedEntityId()
    {
        return $this->_lastProcessedEntityId;
    }

    /**
     * Get progress
     *
     * @return float
     */
    public function getProgress()
    {
        $collection = $this->getProductCollection();
        $total = $collection->getSize();

        return $total ? (1 - round(1.0 * $this->_itemsLeftCount / $total, 2)) : 1.0;
    }
}
