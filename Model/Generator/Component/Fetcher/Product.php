<?php

namespace Doofinder\Feed\Model\Generator\Component\Fetcher;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\FetcherInterface;

/**
 * Product fetcher
 */
class Product extends Component implements FetcherInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productColFactory = null;

    /**
     * @var \Doofinder\Feed\Model\Generator\ItemFactory
     */
    private $generatorItemFactory = null;

    // Ignore protected properties
    // @codingStandardsIgnoreStart
    /**
     * @var boolean
     */
    protected $isStarted = null;

    /**
     * @var boolean
     */
    protected $isDone = null;

    /**
     * @var int
     */
    protected $lastEntityId = null;

    /**
     * Amount of all products left in current fetch
     *
     * @var int
     */
    protected $itemsLeftCount = null;
    // @codingStandardsEnd

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productColFactory,
        \Doofinder\Feed\Model\Generator\ItemFactory $generatorItemFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->productColFactory = $productColFactory;
        $this->generatorItemFactory = $generatorItemFactory;
        parent::__construct($logger, $data);
    }

    /**
     * Fetch products using collection
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    public function fetchProducts()
    {
        $collection = $this->getProductCollection($this->getLimit(), $this->getOffset());
        $collection->load();

        // Check if fetcher is started
        $this->isStarted = !$this->getOffset();

        // Check if fetcher is done
        if ($collection->getSize() > 0) {
            $this->isDone = !$this->getLimit() || $this->getLimit() >= $collection->getSize();
        } else {
            // Done if not items fetched but fetcher is started
            $this->isDone = $this->isStarted;
        }

        // Set the last processed entity id
        $this->lastEntityId = $this->getOffset();
        if ($collection->getSize()) {
            $this->lastEntityId = $collection->getLastItem()->getEntityId();
        }

        // Set fetched size
        $this->itemsLeftCount = $this->getLimit() ? max(0, $collection->getSize() - $this->getLimit()) : 0;

        return $collection->getItems();
    }

    /**
     * Fetch products
     *
     * @return Item[]
     */
    public function fetch()
    {
        $products = $this->fetchProducts();

        $items = [];
        foreach ($products as $product) {
            $item = $this->createItem($product);
            $items[] = $item;

            // Add all item associates inline with other products
            if ($item->hasAssociates()) {
                foreach ($item->getAssociates() as $associate) {
                    $items[] = $associate;
                }
            }
        }

        return $items;
    }

    /**
     * Fetch product associates
     *
     * @param \Magento\Catalog\Model\Product
     * @return \Doofinder\Feed\Model\Generator\Item[]
     */
    private function fetchProductAssociates(\Magento\Catalog\Model\Product $product)
    {
        $associates = [];

        foreach ($product->getTypeInstance()->getUsedProducts($product) as $subproduct) {
            $associates[] = $this->createItem($subproduct);
        }

        return $associates;
    }

    /**
     * Get product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getProductCollection($limit = null, $offset = null)
    {
        $collection = $this->productColFactory->create()
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
            // Transform offset to last processed entity id if offset is from frontend
            if ($this->getTransformOffset()) {
                $offset = $this->getLastProcessedEntityIdFromOffset($offset);
            }

            $collection->addAttributeToFilter('entity_id', ['gt' => $offset]);
        }

        return $collection;
    }

    /**
     * Create item from product
     *
     * @param \Magento\Catalog\Model\Product
     */
    private function createItem(\Magento\Catalog\Model\Product $product)
    {
        $item = $this->generatorItemFactory->create();
        $item->setContext($product);

        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $item->setAssociates($this->fetchProductAssociates($product));
        }

        return $item;
    }

    /**
     * Check if the first item has been fetched
     *
     * @return boolean
     */
    public function isStarted()
    {
        return $this->isStarted;
    }

    /**
     * Check if the last item has been fetched
     *
     * @return boolean
     */
    public function isDone()
    {
        return $this->isDone;
    }

    /**
     * Get last processed entity id
     *
     * @return int
     */
    public function getLastProcessedEntityId()
    {
        return $this->lastEntityId;
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

        return $total ? (1 - round(1.0 * $this->itemsLeftCount / $total, 2)) : 1.0;
    }

    /**
     * Get entity_id offset from query offset
     *
     * @notice This is a workaround for frontend controller
     *         which passes offset as a number of processed
     *         products instead of last processed entity id
     *
     * @return int
     */
    private function getLastProcessedEntityIdFromOffset()
    {
        $collection = $this->getProductCollection();
        $ids = $collection->getAllIds(1, $this->getOffset() - 1);

        return $ids ? reset($ids) : null;
    }
}
