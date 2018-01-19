<?php

namespace Doofinder\Feed\Search;

use Magento\Framework\Indexer\IndexStructureInterface;

/**
 * Index structure
 */
class IndexStructure implements IndexStructureInterface
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexStructure
     */
    private $indexStructure;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $search;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\CatalogSearch\Model\Indexer\IndexStructureFactory $indexStructureFactory
     * @param \Doofinder\Feed\Helper\Search $search
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @SuppressWarnings(PHPMD.LongVariable)
     * @codingStandardsIgnoreStart
     * Ignore MEQP2.Classes.ConstructorOperations.CustomOperationsFound
     */
    public function __construct(
        \Magento\CatalogSearch\Model\Indexer\IndexStructureFactory $indexStructureFactory,
        \Doofinder\Feed\Helper\Search $search,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    // @codingStandardsIgnoreEnd
        $this->indexStructure = $indexStructureFactory->create();
        $this->search = $search;
        $this->storeConfig = $storeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     */
    public function delete($index, array $dimensions = [])
    {
        $this->action('deleteDoofinderIndex', $dimensions);
        $this->indexStructure->delete($index, $dimensions);
    }

    /**
     * @param string $index
     * @param array $fields
     * @param array $dimensions
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        $this->action('createDoofinderIndex', $dimensions);
        $this->indexStructure->create($index, $fields, $dimensions);
    }

    /**
     * Action helper
     *
     * @param string $method
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return void
     */
    private function action($method, array $dimensions)
    {
        $originalStoreCode = $this->storeConfig->getStoreCode();
        $storeId = $this->search->getStoreIdFromDimensions($dimensions);
        $this->storeManager->setCurrentStore($storeId);

        $this->search->{$method}();

        $this->storeManager->setCurrentStore($originalStoreCode);
    }
}
